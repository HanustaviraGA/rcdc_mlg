<?php

namespace App\Services\Publications;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ImportedLecturerKpi
{
    public function rows(): Collection
    {
        $masters = $this->table('database_dosen')->keyBy('kode_dosen');
        $details = $this->table('database_dosen_new')->keyBy('kode_dosen');
        // Keep each monthly snapshot separate: cumulative files must never be summed across months.
        $imports = $this->table('publication_imports')->keyBy('id');
        $researchImports = $this->table('research_imports')->keyBy('id');
        $snapshots = [];
        $key = static fn ($year, $month) => ($year ?: 0).'-'.($month ?: 0);
        foreach ($imports as $batch) {
            $snapshots[$key($batch->year, $batch->month)]['fm'] = true;
        }
        foreach ($researchImports as $batch) {
            $snapshots[$key($batch->year ?? null, $batch->month ?? null)]['research'] = true;
        }
        foreach ($this->table('rectorate_dosen') as $paper) {
            if (! in_array(strtolower(trim($paper->submitted ?? '')), ['scopus fm', 'non scopus fm'], true)) {
                continue;
            }
            $campus = strtolower(trim($paper->kampus ?? ''));
            // The old Malang FM importer did not persist the campus column.
            if ($campus !== '' && $campus !== 'malang') {
                continue;
            }
            $batch = $imports->get($paper->publication_import_id ?? null);
            $snapshot = $key($batch->year ?? $paper->year, $batch->month ?? $paper->month);
            $snapshots[$snapshot]['fm'] = true;
            $snapshots[$snapshot]['papers'][] = $paper;
        }
        foreach ($this->table('publication_kpi_entries') as $entry) {
            $batch = $imports->get($entry->publication_import_id);
            if ($batch) {
                $snapshots[$key($batch->year, $batch->month)]['entries'][] = $entry;
            }
        }
        foreach ($this->table('rectorate_research') as $grant) {
            if (strtoupper(trim($grant->kategori_fm_eksternal_mahasiswa ?? '')) !== 'FM'
                || strtolower(trim($grant->lokasi_kampus ?? '')) !== 'binus @malang') {
                continue;
            }
            $batch = $researchImports->get($grant->research_import_id);
            if ($batch) {
                $snapshots[$key($batch->year ?? null, $batch->month ?? null)]['grants'][] = $grant;
            }
        }
        $rows = collect();
        foreach ($snapshots as $snapshot => $data) {
            [$year, $month] = array_map('intval', explode('-', $snapshot));
            $papers = collect($data['papers'] ?? [])->sortByDesc('updated_at')
                ->unique(fn ($row) => $row->request_code.'|'.$row->kode_dosen)->groupBy('kode_dosen');
            $entries = collect($data['entries'] ?? [])->keyBy('kode_dosen');
            $grants = collect($data['grants'] ?? [])->groupBy('kode_dosen_nim');
            $codes = $entries->keys()->merge($papers->keys())->merge($grants->keys())->unique();
            foreach ($codes as $code) {
                $entry = $entries->get($code);
                $publications = $papers->get($code, collect());
                $research = $grants->get($code, collect());
                $paper = $publications->first();
                $grant = $research->first();
                $master = $masters->get($code);
                $detail = $details->get($code);
                $metrics = PublicationWorkbookMetrics::summarize($publications, $entry);
                $hasPublication = $entry !== null || $publications->isNotEmpty();
                $program = $this->filled($entry->program ?? null, $paper->prodi_kpi ?? null, $grant->prodi_di_kpi ?? null,
                    $detail->nama_gugus_binaan ?? null, $master->jurusan_dosen ?? null, $paper->dept ?? null, $grant->prodi_jur_binaan ?? null);
                $rank = $this->filled($entry->academic_rank ?? null, $paper->jja ?? null, $detail->jja ?? null, $master->jja_dosen ?? null);
                $faculty = $this->filled($entry->faculty_type ?? null, $detail->tipe_faculty ?? null, $master->ft_dosen ?? null);
                $score = $metrics['score'];
                $scoreSource = $entry ? 'RTTO' : null;
                // Legacy files without a KPI sheet retain the existing calculation, scoped to this month.
                if ($entries->isEmpty() && $publications->isNotEmpty()) {
                    $scoring = app(PublicationScore::class);
                    $education = $scoring->education($paper->pendidikan ?? null)
                        ?? $scoring->education($detail->pendidikan ?? null) ?? $scoring->education($master->pendidikan_dosen ?? null);
                    $rule = $scoring->rule($scoring->faculty($faculty), $scoring->rank($rank), $education);
                    $adjusted = $publications->map(function ($row) {
                        $row = clone $row;
                        if (is_numeric($row->bobot_asli)) {
                            $row->bobot = RawPublicationReader::adjustedWeight((array) $row);
                        }

                        return $row;
                    });
                    $score = $scoring->calculate((string) $code, $rule, $adjusted);
                    $scoreSource = $score === null ? null : 'Sistem';
                }
                $rows->push([
                    'year' => $year ?: null, 'month' => $month ?: null, 'code' => (string) $code,
                    'name' => $this->filled($entry->name ?? null, $detail->nama_dosen ?? null, $master->nama_dosen ?? null, $paper->fm_author ?? null, $grant->nama ?? null) ?? $code,
                    'program' => ReportPrograms::code($program) ?: 'Belum tercatat',
                    'program_name' => $program, 'rank' => $rank, 'faculty' => $faculty,
                    'non_scopus' => $hasPublication ? $metrics['non_scopus'] : null,
                    'scopus' => $hasPublication ? $metrics['scopus'] : null,
                    'titles' => $hasPublication ? $metrics['titles'] : null,
                    'first_author' => $hasPublication ? $metrics['first_author'] : null,
                    'score' => $score, 'score_source' => $scoreSource,
                    'rectorate_non_scopus' => $hasPublication ? $metrics['rectorate_non_scopus'] : null,
                    'rectorate_scopus' => $hasPublication ? $metrics['rectorate_scopus'] : null,
                    'rtto_non_scopus' => $metrics['rtto_non_scopus'], 'rtto_scopus' => $metrics['rtto_scopus'],
                    'grant_chair' => isset($data['research']) ? $research->filter(fn ($r) => str_starts_with(strtolower(trim($r->peran)), 'ketua'))->unique('project_key')->count() : null,
                    'grant_member' => isset($data['research']) ? $research->filter(fn ($r) => str_starts_with(strtolower(trim($r->peran)), 'anggota'))->unique('project_key')->count() : null,
                    'grant_years' => $research->pluck('budget_year')->unique()->sort()->implode(', '),
                ]);
            }
        }

        return $rows->sortBy([['year', 'desc'], ['month', 'desc'], ['name', 'asc'], ['code', 'asc']])->values();
    }

    public function filter(Collection $rows, array $filters): Collection
    {
        $search = mb_strtolower(trim($filters['search'] ?? ''));

        return $rows->filter(function ($row) use ($filters, $search) {
            return (empty($filters['year']) || $row['year'] === (int) $filters['year'])
                && (empty($filters['month']) || $row['month'] === (int) $filters['month'])
                && (empty($filters['prodi']) || $row['program'] === $filters['prodi'])
                && ($search === '' || str_contains(mb_strtolower(implode(' ', [
                    $row['code'], $row['name'], $row['program'], $row['program_name'], $row['rank'], $row['faculty'],
                ])), $search));
        })->values();
    }

    private function filled(?string ...$values): ?string
    {
        foreach ($values as $value) {
            if (trim($value ?? '') !== '') {
                return trim($value);
            }
        }

        return null;
    }

    private function table(string $name): Collection
    {
        return Schema::hasTable($name) ? DB::table($name)->get() : collect();
    }
}
