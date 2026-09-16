<?php

namespace App\Services\Research;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ResearchGallery
{
    public function __construct(private RectorateResearchRepository $rectorate) {}

    public function projects(): Collection
    {
        return $this->rectorateProjects()->concat($this->systemProjects());
    }

    public function rectorateProjects(): Collection
    {
        return $this->rectorate->activeRows()->groupBy('project_key')->map(function ($rows, $key) {
            $first = $rows->first();
            $variants = [];
            foreach (['judul', 'program_hibah', 'skema', 'approved_amount', 'starts_on', 'ends_on'] as $field) {
                $values = $this->values($rows, $field);
                if (count($values) > 1) {
                    $variants[$field] = $values;
                }
            }
            $fields = $this->values($rows, 'bidang_ilmu_by_qs_subject');
            $fields = array_values(array_unique(array_map(fn ($field) => str_replace(' & ', ' and ', $field), $fields)));
            $sdgs = $rows->flatMap(fn ($row) => json_decode($row->sdg_numbers ?? '[]', true) ?: [])->unique()->sort()->values()->all();
            $participants = $rows->map(fn ($row) => [
                'code' => $row->kode_dosen_nim, 'name' => $row->nama, 'role' => $row->peran,
                'category' => $row->kategori_fm_eksternal_mahasiswa, 'program' => $row->prodi_di_kpi,
                'research_kpi' => $row->pengakuan_kpi_research_program,
            ])->unique(fn ($person) => $person['code'].'|'.$person['role'])->values()->all();
            $amounts = $this->values($rows, 'approved_amount');
            $begin = $this->values($rows, 'starts_on');
            $end = $this->values($rows, 'ends_on');

            return [
                'id' => $key, 'source' => 'rectorate', 'source_label' => 'Upload Rectorate',
                'title' => $first->judul, 'code' => $first->kd_prop, 'year' => (int) $first->budget_year,
                'fields' => $fields, 'faculties' => $this->values($rows, 'fakultas_kpi'),
                'programs' => $this->values($rows, 'prodi_di_kpi'), 'people' => $participants,
                'funds' => $this->values($rows, 'sumber_dana'), 'grant_programs' => $this->values($rows, 'program_hibah'),
                'schemes' => $this->values($rows, 'skema'), 'funders' => $this->values($rows, 'nama_pemberi_hibah'),
                'amount' => count($amounts) === 1 ? $amounts[0] : null,
                'start' => count($begin) === 1 ? $begin[0] : null, 'end' => count($end) === 1 ? $end[0] : null,
                'statuses' => $this->values($rows, 'status_usulan'), 'types' => $this->values($rows, 'jenis_penelitian'),
                'sdgs' => $sdgs, 'outputs' => $this->values($rows, 'produk_yang_dihasilkan'),
                'sdg_notes' => $this->values($rows, 'keyword_sdgs'), 'roadmap' => $this->values($rows, 'subtopik_research_roadmap'),
                'abstracts' => [], 'variants' => $variants,
                'row_issues' => $rows->flatMap(fn ($row) => json_decode($row->data_issues ?? '[]', true) ?: [])->unique()->values()->all(),
                'permalink' => null, 'campus' => RectorateResearchReader::CAMPUS,
                'updated_at' => $rows->max('updated_at'), 'rows' => $rows->count(),
            ];
        })->values();
    }

    private function systemProjects(): Collection
    {
        if (! Schema::hasTable('researchs')) {
            return collect();
        }
        $master = Schema::hasTable('database_dosen_new') ? DB::table('database_dosen_new')
            ->select('kode_dosen', 'nama_dosen', 'nama_gugus_binaan', 'fakultas_internal')->get()->keyBy('kode_dosen') : collect();
        $parser = new RectorateResearchReader;

        // A contract can cover many distinct projects. The system ID is its identity.
        return DB::table('researchs')->orderByDesc('updated_at')->get()
            ->groupBy(fn ($row) => hash('sha256', 'system|'.$row->ID))->map(function ($rows, $key) use ($master, $parser) {
                $first = $rows->first();
                $people = collect();
                foreach ($rows as $row) {
                    $researchers = json_decode($row->researcher ?? '[]', true);
                    foreach (is_array($researchers) ? $researchers : [] as $person) {
                        if (! is_array($person)) {
                            continue;
                        }
                        $code = (string) ($person['ID'] ?? '');
                        $people->push(['code' => $code, 'name' => $person['name'] ?? $code,
                            'role' => 'Peneliti · peran tidak tersedia', 'program' => $master->get($code)->nama_gugus_binaan ?? null]);
                    }
                    if ($row->kode_dosen && ! $people->contains('code', $row->kode_dosen)) {
                        $people->push(['code' => $row->kode_dosen, 'name' => $master->get($row->kode_dosen)->nama_dosen ?? $row->kode_dosen,
                            'role' => 'Peneliti · peran tidak tersedia', 'program' => $master->get($row->kode_dosen)->nama_gugus_binaan ?? null]);
                    }
                }
                $people = $people->unique(fn ($person) => $person['code'] ?: $person['name'])->values();
                $amounts = $rows->map(fn ($row) => $parser->amount($row->funding))->filter(fn ($amount) => $amount !== null)->unique()->values();
                $link = $first->permalink;
                $validLink = is_string($link) && filter_var($link, FILTER_VALIDATE_URL) && in_array(strtolower(parse_url($link, PHP_URL_SCHEME) ?? ''), ['http', 'https'], true);

                return [
                    'id' => $key, 'source' => 'system', 'source_label' => 'Sistem Riset', 'title' => $first->title,
                    'code' => $first->contract_number, 'year' => (int) $first->budget_year,
                    'fields' => [], 'faculties' => $people->map(fn ($p) => $master->get($p['code'])->fakultas_internal ?? null)->filter()->unique()->values()->all(),
                    'programs' => $people->pluck('program')->filter()->unique()->values()->all(), 'people' => $people->all(),
                    'funds' => $this->values($rows, 'source_of_fund'), 'grant_programs' => [], 'schemes' => [],
                    'funders' => $this->values($rows, 'institution'), 'amount' => $amounts->count() === 1 ? $amounts->first() : null,
                    'start' => null, 'end' => null, 'statuses' => [], 'types' => [], 'sdgs' => [], 'outputs' => [],
                    'sdg_notes' => [], 'roadmap' => [], 'abstracts' => $this->values($rows, 'abstract'),
                    'variants' => [], 'row_issues' => [], 'permalink' => $validLink ? $link : null,
                    'campus' => null, 'updated_at' => $rows->max('updated_at'), 'rows' => $rows->count(),
                ];
            })->values();
    }

    private function values(Collection $rows, string $field): array
    {
        return $rows->pluck($field)->filter(fn ($value) => $value !== null && trim((string) $value) !== '' && trim((string) $value) !== '-')
            ->map(fn ($value) => trim((string) $value))->unique()->values()->all();
    }
}
