<?php

namespace App\Services\Publications;

use App\Services\Lecturers\LecturerSnapshots;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PublicationPerformanceDashboard
{
    public function __construct(private PublicationDashboard $dashboard, private LecturerSnapshots $lecturerSnapshots) {}

    public function data(): array
    {
        $original = $this->dashboard->data();
        $faculty = collect($original['faculty'])->keyBy('code');
        $masters = $this->rows('database_dosen')->keyBy('kode_dosen');
        $campuses = $this->rows('database_dosen_new')->keyBy('kode_dosen');
        $imports = $this->rows('publication_imports');
        $entries = $this->rows('publication_kpi_entries')->groupBy('publication_import_id');
        $rawPapers = $this->rows('rectorate_dosen');
        $hasLecturerSnapshots = Schema::hasTable('lecturer_imports');
        $historicalProfilesByPeriod = [];
        $paperPeriods = $rawPapers->groupBy(fn ($row) => $row->year.'|'.$row->month);
        if ($hasLecturerSnapshots) {
            foreach ($paperPeriods->keys() as $period) {
                [$year, $month] = array_map('intval', explode('|', $period));
                $historicalProfilesByPeriod[$period] = $this->periodProfiles($year, $month);
            }
        }
        $papers = $rawPapers->filter(function ($row) use ($masters, $campuses, $historicalProfilesByPeriod, $hasLecturerSnapshots) {
            if (! in_array($row->submitted, RawPublicationReader::SUBMITTED, true)) {
                return false;
            }
            $campus = strtolower(trim($row->kampus ?? ''));
            $historical = ($historicalProfilesByPeriod[$row->year.'|'.$row->month] ?? collect())->get($row->kode_dosen);
            // Once snapshots are installed, an empty legacy campus is not
            // allowed to fall back to the current master roster.
            $profileCampus = $historical?->campus ?? ($hasLecturerSnapshots ? '' : $campuses->get($row->kode_dosen)->campus ?? '');

            return $campus === 'malang' || ($campus === '' && ($hasLecturerSnapshots
                ? $historical !== null && str_contains(strtolower($profileCampus), 'malang')
                : $masters->has($row->kode_dosen) && str_contains(strtolower($profileCampus), 'malang')));
        });
        $snapshots = [];
        $kpiBatches = $imports->filter(fn ($batch) => $entries->get($batch->id, collect())->isNotEmpty());
        $periods = $papers->groupBy(fn ($row) => $row->year.'|'.$row->month)->keys()
            ->merge($kpiBatches->map(fn ($batch) => $batch->year.'|'.$batch->month))->unique();
        foreach ($periods as $key) {
            [$year, $month] = array_map('intval', explode('|', $key));
            $periodRows = $papers->where('year', $year)->where('month', $month);
            $periodBatches = $imports->where('year', $year)->where('month', $month);
            $latestPeriod = $periodRows->pluck('period')->merge($kpiBatches->where('year', $year)
                ->where('month', $month)->pluck('period'))->max();
            $current = $periodRows->where('period', $latestPeriod)->sortByDesc('updated_at')
                ->unique(fn ($row) => PublicationWorkbookMetrics::publicationKey($row).'|'.$row->kode_dosen)->values();
            $batch = $periodBatches->firstWhere('period', $latestPeriod);
            // Snapshot tables are optional while older installations and tests are upgraded.
            $historicalProfiles = $hasLecturerSnapshots ? $this->periodProfiles($year, $month) : collect();
            $snapshots[$key] = ['year' => $year, 'month' => $month,
                'fm' => $this->facultySnapshot($current, $entries->get($batch->id ?? null, collect()), $faculty,
                    $hasLecturerSnapshots ? collect() : $campuses, $historicalProfiles, $batch, ! $hasLecturerSnapshots)];
        }
        foreach ($this->rows('student_publication_imports') as $batch) {
            $key = $batch->year.'|'.$batch->month;
            $snapshots[$key] ??= ['year' => (int) $batch->year, 'month' => (int) $batch->month];
            $snapshots[$key]['mhs'] = $this->studentSnapshot($batch);
        }
        $snapshots = collect($snapshots)->sortBy(fn ($row) => $row['year'] * 100 + $row['month'])->values();
        $years = collect($original['years'])->merge($snapshots->pluck('year'))->push((int) now()->year)->map(fn ($year) => (string) $year)
            ->unique()->sort()->values();

        return ['years' => $years->all(), 'defaultYear' => (string) now()->year,
            'snapshots' => $snapshots->all(), 'programs' => collect(ReportPrograms::FULL_LABELS)->except('BINUS')->all()];
    }

    private function facultySnapshot(Collection $papers, Collection $entries, Collection $faculty,
        Collection $currentProfiles, Collection $historicalProfiles, ?object $batch, bool $allowCurrentFallback): array
    {
        $summary = json_decode($batch->summary ?? '{}', true) ?: [];
        $byLecturer = $papers->groupBy('kode_dosen');
        $byCode = $entries->keyBy('kode_dosen');
        $members = $byCode->keys()->merge($byLecturer->keys())->unique()->map(function ($code) use (
            $byCode, $byLecturer, $faculty, $currentProfiles, $historicalProfiles, $allowCurrentFallback
        ) {
            $entry = $byCode->get($code);
            $rows = $byLecturer->get($code, collect());
            $current = $faculty->get($code, []);
            $currentProfile = $currentProfiles->get($code);
            $historical = $historicalProfiles->get($code);
            // Once snapshot storage is installed, historical cards must not
            // fall back to the current roster when that period has no row.
            $legacy = $allowCurrentFallback ? $current : [];
            $legacyCampus = $allowCurrentFallback ? $currentProfile?->campus : null;
            $paper = $rows->first();
            $metrics = PublicationWorkbookMetrics::summarize($rows, $entry);

            // KPI workbook fields and publication-row fields are authoritative. The
            // lecturer snapshot is only the period-appropriate profile fallback;
            // current master data must not rewrite historical cards.
            return ['code' => (string) $code,
                'name' => $this->profileValue($entry?->name, $paper?->fm_author, $historical?->nama_dosen,
                    $legacy['name'] ?? null, (string) $code),
                'program' => ReportPrograms::code($this->profileValue($entry?->program, $paper?->prodi_kpi, $paper?->dept,
                    $historical?->nama_gugus_binaan, $historical?->nama_program, $legacy['program'] ?? null, 'Lainnya')),
                'rank' => $this->profileValue($entry?->academic_rank, $paper?->jja, $historical?->jja, $legacy['rank'] ?? null),
                'faculty' => $this->profileValue($entry?->faculty_type, $historical?->tipe_faculty, $legacy['faculty_detail'] ?? null),
                'campus' => $this->profileValue($paper?->kampus, $historical?->campus, $legacyCampus),
                'nonScopus' => $metrics['non_scopus'], 'scopus' => $metrics['scopus'],
                'firstAuthor' => $metrics['first_author'], 'score' => $metrics['score']];
        })->values();
        $metadata = $summary['report'] ?? [];
        $memberPrograms = $members->pluck('program', 'code');
        $paperProgram = fn ($row) => ReportPrograms::code($this->profileValue($row->prodi_kpi ?? null, $row->dept ?? null,
            $memberPrograms->get($row->kode_dosen), 'Lainnya'));
        $programs = $members->pluck('program')->merge($papers->map($paperProgram))
            ->merge(array_keys($metadata['realization'] ?? []))->reject(fn ($code) => ! $code || $code === 'BINUS')->unique();
        $cards = $programs->map(function ($program) use ($members, $papers, $metadata, $paperProgram) {
            $roster = $members->where('program', $program)->values();
            $rows = $papers->filter(fn ($row) => $paperProgram($row) === $program);
            $scopus = $rows->filter(PublicationWorkbookMetrics::isScopusTitle(...));
            $actual = $metadata['realization'][$program]['realization'] ?? $scopus->sum('bobot_asli');

            return $this->card($program, $metadata, (float) $actual) + ['faculty' => $roster->all(),
                'facultyTotal' => $roster->count(), 'scopusFaculty' => $roster->where('scopus', '>', 0)->count(),
                'titles' => $scopus->count(), 'weight' => (float) $scopus->sum('bobot_asli'),
                'breakdown' => collect(['Jurnal' => 'Jurnal', 'Seminar' => 'Konferensi', 'Book Chapter' => 'Book chapter', 'Full Book' => 'Full book'])
                    ->map(fn ($label, $type) => ['label' => $label,
                        'weight' => (float) $scopus->filter(fn ($row) => strcasecmp(trim($row->jenis), $type) === 0)->sum('bobot_asli'),
                        'titles' => $scopus->filter(fn ($row) => strcasecmp(trim($row->jenis), $type) === 0)->count()])->values()->all(),
                'publications' => $rows->map(fn ($row) => ['code' => $row->kode_dosen, 'request' => $row->request_code,
                    'author' => $row->author, 'name' => $row->fm_author, 'title' => $row->title,
                    'firstAuthor' => $row->first_author, 'weight' => $row->bobot_asli, 'category' => $row->submitted,
                    'status' => $row->status, 'type' => $row->jenis, 'index' => $row->tipe_publikasi,
                    'source' => $row->source_title, 'paperSource' => $row->sumber_paper ?? null,
                    'quartile' => $row->quartile_jurnal])->values()->all()];
        })->sortBy(fn ($card) => array_search($card['program'], array_keys(ReportPrograms::LABELS), true) ?: PHP_INT_MAX)->values();
        $actual = $metadata['realization']['BINUS']['realization'] ?? $papers->filter(PublicationWorkbookMetrics::isScopusTitle(...))->sum('bobot_asli');

        return $this->source($batch, $summary) + ['target' => $metadata['realization']['BINUS']['target'] ?? null,
            'actual' => (float) $actual, 'unit' => 'bobot', 'cards' => $cards->all(),
            'facultyTotal' => $members->count(), 'scopusFaculty' => $members->where('scopus', '>', 0)->count(),
            'populationLabel' => $entries->isNotEmpty() ? 'Daftar dosen KPI dan publikasi pada periode ini' : 'Dosen dalam snapshot publikasi',
            'actualSource' => isset($metadata['realization']['BINUS']['realization']) ? 'Rekap workbook' : 'Bobot asli publikasi'];
    }

    private function studentSnapshot(object $batch): array
    {
        $summary = json_decode($batch->summary, true) ?: [];
        $list = collect(json_decode($batch->publication_list, true) ?: []);
        $metadata = $summary['report'] ?? [];
        $counts = ($summary['title_counts'] ?? []) ?: ($summary['list_counts'] ?? []);
        $programs = $list->map(fn ($row) => ReportPrograms::code($row['program']))
            ->merge(array_keys($metadata['realization'] ?? []))->merge(array_keys($counts))
            ->reject(fn ($code) => ! $code || $code === 'BINUS')->unique();
        $cards = $programs->map(function ($program) use ($list, $counts, $metadata) {
            $rows = $list->filter(fn ($row) => ReportPrograms::code($row['program']) === $program)->values();

            return $this->card($program, $metadata, (float) ($counts[$program] ?? $rows->count()))
                + ['students' => $rows->map(fn ($row) => ['name' => $row['name'], 'title' => $row['title']])->all(),
                    'authors' => $rows->pluck('name')->map(fn ($name) => mb_strtolower(trim($name)))->unique()->count(),
                    'titles' => (int) ($counts[$program] ?? $rows->count())];
        })->sortBy(fn ($card) => array_search($card['program'], array_keys(ReportPrograms::LABELS), true) ?: PHP_INT_MAX)->values();

        return $this->source($batch, $summary) + ['target' => $metadata['realization']['BINUS']['target'] ?? null,
            'actual' => (float) ($counts['BINUS'] ?? $list->count()), 'unit' => 'judul', 'cards' => $cards->all(),
            'actualSource' => 'Rekap TITLE / LIST'];
    }

    private function card(string $program, array $metadata, float $actual): array
    {
        return ['program' => $program, 'label' => ReportPrograms::FULL_LABELS[$program] ?? $program,
            'target' => $metadata['realization'][$program]['target'] ?? null, 'actual' => $actual,
            'score' => ReportWorkbookReader::score($actual, $metadata['ranges'][$program] ?? [])];
    }

    private function source(?object $batch, array $summary): array
    {
        return ['updatedAt' => $batch->updated_at ?? null, 'filename' => isset($batch->filename) ? basename($batch->filename) : null,
            'warnings' => array_values(array_filter($summary['warnings'] ?? [], 'is_string'))];
    }

    private function profileValue(?string ...$values): ?string
    {
        foreach ($values as $value) {
            if ($value !== null && trim($value) !== '') {
                return $value;
            }
        }

        return null;
    }

    private function periodProfiles(int $year, int $month): Collection
    {
        $batch = $this->lecturerSnapshots->batch($year, $month);

        return $batch ? $this->lecturerSnapshots->rows($year, (int) $batch->month) : collect();
    }

    private function rows(string $table): Collection
    {
        return Schema::hasTable($table) ? DB::table($table)->get() : collect();
    }
}
