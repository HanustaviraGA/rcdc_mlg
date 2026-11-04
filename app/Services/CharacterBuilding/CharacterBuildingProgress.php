<?php

namespace App\Services\CharacterBuilding;

use App\Services\CommunityDevelopment\CommunityDevelopmentDashboard;
use App\Services\Publications\ImportedLecturerKpi;
use App\Services\Publications\PublicationWorkbookMetrics;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Realization of the Character Building (CBDC) targets per quarter, for CBDC as a whole:
 * Scopus titles from the ImportRectorate FM uploads and comdev activities from the ImportPKM
 * uploads. The targets of the year form its score ranges, and the realization earns the score
 * of the highest target it reaches.
 */
class CharacterBuildingProgress
{
    private const SUBMITTED = ['scopus fm', 'non scopus fm'];

    public function __construct(private CharacterBuildingTargets $targets) {}

    /**
     * CBDC publications in the last FM upload of the quarter. FM files are cumulative within a
     * year, so that upload shows the position at the end of the quarter.
     */
    public function publication(int $year, int $quarter): array
    {
        $batch = Schema::hasTable('publication_imports') ? DB::table('publication_imports')->where('year', $year)
            ->whereBetween('month', [3 * $quarter - 2, 3 * $quarter])->orderByDesc('month')->first() : null;
        $lecturers = collect();
        $publications = collect();
        if ($batch) {
            // As on the Dashboard Riset: lecturers on the KPI sheet or with publications, not those with grants only.
            $lecturers = app(ImportedLecturerKpi::class)->rows()
                ->where('year', $year)->where('month', (int) $batch->month)->where('program', 'CBDC')
                ->filter(fn ($row) => $row['titles'] !== null)->sortBy('name')->values();
            $publications = $this->publications($batch, $lecturers->pluck('name', 'code'));
        }
        // A Scopus title written by several CBDC lecturers counts once for CBDC.
        $scopus = $publications->where('scopus', true)->unique('key');

        return [
            'year' => $year, 'quarter' => $quarter, 'label' => "{$year} Q{$quarter}",
            'batch' => $batch, 'lecturers' => $lecturers, 'publications' => $publications,
            'summary' => $this->summary('publikasi', $year, $quarter, $batch ? $scopus->count() : null),
        ];
    }

    /**
     * CBDC comdev in the semester holding most of the quarter's months. ImportPKM uploads one
     * file per semester (Ganjil September–February, Genap March–August).
     */
    public function comdev(int $year, int $quarter): array
    {
        [$yearStart, $semester] = self::semesterFor($year, $quarter);
        $batch = Schema::hasTable('community_development_imports')
            ? DB::table('community_development_imports')->where('year_start', $yearStart)->where('semester', $semester)->first() : null;
        $program = null;
        $lecturers = collect();
        $activities = null;
        $source = null;
        if ($batch) {
            $program = collect(app(CommunityDevelopmentDashboard::class)->data($batch->id)['programs'])->firstWhere('key', 'cbdc');
            $lecturers = collect($program['lecturers'] ?? [])->sortBy('name')->values();
            // The CBDC row of the Rasio sheet; without one, the activities its lecturers reported.
            if (($program['activities'] ?? null) !== null) {
                [$activities, $source] = [(int) $program['activities'], 'Rasio'];
            } elseif ($lecturers->isNotEmpty()) {
                [$activities, $source] = [(int) $lecturers->sum('activities'), 'Keterlibatan'];
            }
        }

        return [
            'year' => $year, 'quarter' => $quarter, 'label' => "{$year} Q{$quarter}",
            'semester' => $yearStart.'/'.($yearStart + 1).' '.$semester,
            'semester_months' => $semester === 'Ganjil' ? 'September–Februari' : 'Maret–Agustus',
            'batch' => $batch, 'program' => $program, 'lecturers' => $lecturers, 'source' => $source,
            'summary' => $this->summary('comdev', $year, $quarter, $activities),
        ];
    }

    /** @return array{0: int, 1: string} first year of the academic year, and its semester */
    public static function semesterFor(int $year, int $quarter): array
    {
        return match ($quarter) {
            1 => [$year - 1, 'Ganjil'],
            2, 3 => [$year - 1, 'Genap'],
            default => [$year, 'Ganjil'],
        };
    }

    /**
     * The latest quarter (up to now) with data for the tab; the current quarter without any.
     *
     * @return array{0: int, 1: int}
     */
    public function defaultQuarter(string $category): array
    {
        $current = [(int) now()->year, (int) ceil(now()->month / 3)];
        if ($category === 'publikasi' && Schema::hasTable('publication_imports')) {
            $latest = DB::table('publication_imports')->where(fn ($query) => $query->where('year', '<', $current[0])
                ->orWhere(fn ($query) => $query->where('year', $current[0])->where('month', '<=', now()->month)))
                ->orderByDesc('year')->orderByDesc('month')->first();

            return $latest ? [(int) $latest->year, (int) ceil($latest->month / 3)] : $current;
        }
        if ($category === 'comdev' && Schema::hasTable('community_development_imports')) {
            $periods = DB::table('community_development_imports')->get(['year_start', 'semester'])
                ->map(fn ($batch) => $batch->year_start.' '.$batch->semester)->all();
            [$year, $quarter] = $current;
            for ($step = 0; $step < 24; $step++) {
                [$start, $semester] = self::semesterFor($year, $quarter);
                if (in_array("{$start} {$semester}", $periods, true)) {
                    return [$year, $quarter];
                }
                [$year, $quarter] = $quarter === 1 ? [$year - 1, 4] : [$year, $quarter - 1];
            }
        }

        return $current;
    }

    /** Years for the tab filters: those with uploads, plus the target years. */
    public function years(): array
    {
        $extra = Schema::hasTable('publication_imports') ? DB::table('publication_imports')->pluck('year')->all() : [];
        if (Schema::hasTable('community_development_imports')) {
            foreach (DB::table('community_development_imports')->pluck('year_start') as $start) {
                array_push($extra, (int) $start, (int) $start + 1);
            }
        }

        return $this->targets->years(array_map('intval', $extra));
    }

    private function publications(object $batch, Collection $names): Collection
    {
        return DB::table('rectorate_dosen')->where('publication_import_id', $batch->id)->whereIn('kode_dosen', $names->keys())->get()
            ->filter(fn ($row) => in_array(strtolower(trim($row->submitted ?? '')), self::SUBMITTED, true)
                && in_array(strtolower(trim($row->kampus ?? '')), ['', 'malang'], true))
            // One row per lecturer and publication, as in the KPI calculation.
            ->unique(fn ($row) => PublicationWorkbookMetrics::publicationKey($row).'|'.$row->kode_dosen)
            ->map(fn ($row) => [
                'key' => PublicationWorkbookMetrics::publicationKey($row), 'scopus' => PublicationWorkbookMetrics::isScopusTitle($row),
                'code' => $row->kode_dosen, 'name' => $names->get($row->kode_dosen) ?: $row->kode_dosen, 'title' => $row->title,
                'submitted' => $row->submitted, 'type' => $row->tipe_publikasi, 'kind' => $row->jenis,
                'quartile' => $row->quartile_jurnal, 'first_author' => $row->first_author,
                'request' => $row->request_code ?? null, 'author' => $row->author ?? null, 'fm_author' => $row->fm_author ?? null,
                'weight' => $row->bobot_asli ?? null, 'status' => $row->status ?? null, 'source' => $row->source_title ?? null,
                'paper_source' => $row->sumber_paper ?? null,
            ])
            ->sortBy([['scopus', 'desc'], ['name', 'asc'], ['title', 'asc']])->values();
    }

    /**
     * CBDC's realization against the score ranges of the year: the range it reaches (the highest
     * target at or below it) gives the score, and the next range shows what remains to reach.
     * The quarter's own target is shown beside them.
     */
    private function summary(string $category, int $year, int $quarter, ?float $realization): array
    {
        $ladder = $this->targets->ladder($category, $year);
        $reached = CharacterBuildingTargets::reached($ladder, $realization);
        $next = $realization === null ? null : $ladder->first(fn ($tier) => $tier->target > $realization);

        return [
            'realization' => $realization, 'ladder' => $ladder, 'target' => $this->targets->target($category, $year, $quarter),
            'reached' => $reached, 'next' => $next, 'gap' => $next ? round($next->target - $realization, 2) : null,
            'earned' => $reached?->score,
        ];
    }
}
