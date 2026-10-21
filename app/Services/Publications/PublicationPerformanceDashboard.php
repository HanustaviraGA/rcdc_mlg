<?php

namespace App\Services\Publications;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PublicationPerformanceDashboard
{
    public function __construct(private PublicationDashboard $dashboard) {}

    public function data(): array
    {
        $original = $this->dashboard->data();
        $faculty = collect($original['faculty'])->keyBy('code');
        $masters = $this->rows('database_dosen')->keyBy('kode_dosen');
        $campuses = $this->rows('database_dosen_new')->keyBy('kode_dosen');
        $imports = $this->rows('publication_imports');
        $entries = $this->rows('publication_kpi_entries')->groupBy('publication_import_id');
        $papers = $this->rows('rectorate_dosen')->filter(function ($row) use ($masters, $campuses) {
            if (! in_array($row->submitted, RawPublicationReader::SUBMITTED, true)) {
                return false;
            }
            $campus = strtolower(trim($row->kampus ?? ''));

            return $campus === 'malang' || ($campus === '' && $masters->has($row->kode_dosen)
                && str_contains(strtolower($campuses->get($row->kode_dosen)->campus ?? ''), 'malang'));
        });
        $snapshots = [];
        foreach ($papers->groupBy(fn ($row) => $row->year.'|'.$row->month) as $rows) {
            $latest = $rows->sortByDesc('period')->first();
            $current = $rows->where('period', $latest->period)->sortByDesc('updated_at')
                ->unique(fn ($row) => $row->request_code.'|'.$row->kode_dosen)->values();
            $batch = $imports->where('year', $latest->year)->where('month', $latest->month)
                ->where('period', $latest->period)->first();
            $key = $latest->year.'|'.$latest->month;
            $snapshots[$key] = ['year' => (int) $latest->year, 'month' => (int) $latest->month,
                'fm' => $this->facultySnapshot($current, $entries->get($batch->id ?? null, collect()), $faculty, $batch)];
        }
        foreach ($this->rows('student_publication_imports') as $batch) {
            $key = $batch->year.'|'.$batch->month;
            $snapshots[$key] ??= ['year' => (int) $batch->year, 'month' => (int) $batch->month];
            $snapshots[$key]['mhs'] = $this->studentSnapshot($batch);
        }
        $snapshots = collect($snapshots)->sortBy(fn ($row) => $row['year'] * 100 + $row['month'])->values();
        $years = collect($original['years'])->merge($snapshots->pluck('year'))->map(fn ($year) => (string) $year)
            ->unique()->sort()->values();

        return ['years' => $years->all(), 'defaultYear' => (string) ($snapshots->last()['year'] ?? $years->last() ?? now()->year),
            'snapshots' => $snapshots->all(), 'programs' => collect(ReportPrograms::LABELS)->except('BINUS')->all()];
    }

    private function facultySnapshot(Collection $papers, Collection $entries, Collection $faculty, ?object $batch): array
    {
        $summary = json_decode($batch->summary ?? '{}', true) ?: [];
        $byLecturer = $papers->groupBy('kode_dosen');
        $byCode = $entries->keyBy('kode_dosen');
        $members = $byCode->keys()->merge($byLecturer->keys())->unique()->map(function ($code) use ($byCode, $byLecturer, $faculty) {
            $entry = $byCode->get($code);
            $rows = $byLecturer->get($code, collect());
            $profile = $faculty->get($code);
            $paper = $rows->first();
            $metrics = PublicationWorkbookMetrics::summarize($rows, $entry);

            return ['code' => (string) $code, 'name' => $entry->name ?? $profile['name'] ?? $paper->fm_author ?? $code,
                'program' => ReportPrograms::code($entry->program ?? $paper->prodi_kpi ?? $profile['program'] ?? $paper->dept ?? 'Lainnya'),
                'rank' => $entry->academic_rank ?? $profile['rank'] ?? $paper->jja ?? null,
                'faculty' => $entry->faculty_type ?? $profile['faculty_detail'] ?? null,
                'nonScopus' => $metrics['non_scopus'], 'scopus' => $metrics['scopus'],
                'firstAuthor' => $metrics['first_author'], 'score' => $metrics['score']];
        })->values();
        $metadata = $summary['report'] ?? [];
        $programs = $members->pluck('program')->merge($papers->map(fn ($row) => ReportPrograms::code($row->prodi_kpi ?? $row->dept)))
            ->merge(array_keys($metadata['realization'] ?? []))->reject(fn ($code) => ! $code || $code === 'BINUS')->unique();
        $cards = $programs->map(function ($program) use ($members, $papers, $metadata) {
            $roster = $members->where('program', $program)->values();
            $rows = $papers->filter(fn ($row) => ReportPrograms::code($row->prodi_kpi ?? $row->dept) === $program);
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
                    'source' => $row->source_title, 'quartile' => $row->quartile_jurnal])->values()->all()];
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
        return ['program' => $program, 'label' => ReportPrograms::LABELS[$program] ?? $program,
            'target' => $metadata['realization'][$program]['target'] ?? null, 'actual' => $actual,
            'score' => ReportWorkbookReader::score($actual, $metadata['ranges'][$program] ?? [])];
    }

    private function source(?object $batch, array $summary): array
    {
        return ['updatedAt' => $batch->updated_at ?? null, 'filename' => isset($batch->filename) ? basename($batch->filename) : null,
            'warnings' => array_values(array_filter($summary['warnings'] ?? [], 'is_string'))];
    }

    private function rows(string $table): Collection
    {
        return Schema::hasTable($table) ? DB::table($table)->get() : collect();
    }
}
