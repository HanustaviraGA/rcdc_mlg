<?php

namespace App\Services\CommunityDevelopment;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CommunityDevelopmentDashboard
{
    public function __construct(private CommunityDevelopmentDemo $demo) {}

    public function data(?int $period = null, ?int $partnerYear = null): array
    {
        $data = array_replace($this->demo->data(), $this->partners($partnerYear));
        $batches = Schema::hasTable('community_development_imports')
            ? DB::table('community_development_imports')->orderByDesc('year_start')->orderBy('semester')->get() : collect();
        // Genap follows Ganjil within the same academic year.
        $batches = $batches->sortByDesc(fn ($batch) => $batch->year_start * 2 + ($batch->semester === 'Genap' ? 1 : 0))->values();
        $data += ['periods' => $batches->map(fn ($batch) => ['id' => $batch->id, 'label' => $this->periodLabel($batch)])->all(),
            'selectedPeriod' => null, 'periodLabel' => 'Data contoh', 'unassignedLecturers' => [], 'filename' => null];
        if ($batches->isEmpty() && $period === null) {
            return $data;
        }
        $batch = $period === null ? $batches->first() : $batches->firstWhere('id', $period);
        $data['isDemo'] = false;
        $data['programs'] = [];
        $data['totals'] = ['faculty' => null, 'activities' => null, 'ratio' => null];
        if (! $batch) {
            $data['periodLabel'] = 'Periode tidak tersedia';

            return $data;
        }
        $data['selectedPeriod'] = $batch->id;
        $data['periodLabel'] = $this->periodLabel($batch);
        $data['filename'] = $batch->filename;
        $profiles = Schema::hasTable('database_dosen_new')
            ? DB::table('database_dosen_new')->get()->keyBy(fn ($row) => strtoupper(trim($row->kode_dosen))) : collect();
        $lecturers = [];
        foreach (DB::table('community_development_lecturers')->where('import_id', $batch->id)->get() as $row) {
            $profile = $profiles->get($row->kode_dosen);
            $key = $profile && trim($profile->nama_gugus_binaan ?? '') !== ''
                ? CommunityDevelopmentPrograms::key($profile->nama_gugus_binaan) : '_unassigned';
            $lecturers[$key][] = [
                'code' => $row->kode_dosen, 'name' => $profile?->nama_dosen ?: 'Belum terhubung ke data dosen',
                'activities' => $row->reported_activities === null ? null : (int) $row->reported_activities,
                'score' => $row->score === null ? null : (float) $row->score, 'status' => $row->status,
                'matched' => $profile !== null,
            ];
        }
        $total = null;
        foreach (DB::table('community_development_programs')->where('import_id', $batch->id)->orderBy('source_row')->get() as $row) {
            $faculty = $row->faculty === null ? null : (int) $row->faculty;
            $activities = $row->activities === null ? null : (int) $row->activities;
            $values = compact('faculty', 'activities') + ['ratio' => $row->ratio === null
                ? ($faculty > 0 && $activities !== null ? $activities / $faculty : null) : (float) $row->ratio];
            if ($row->is_total) {
                $total = $values;

                continue;
            }
            $data['programs'][] = $values + ['key' => $row->program_key, 'code' => $row->source_program,
                'label' => CommunityDevelopmentPrograms::label($row->program_key, $row->source_program),
                'lecturers' => $lecturers[$row->program_key] ?? []];
            unset($lecturers[$row->program_key]);
        }
        $data['unassignedLecturers'] = array_merge([], ...array_values($lecturers));
        if ($total === null) {
            $sum = fn ($field) => in_array(null, array_column($data['programs'], $field), true) ? null : array_sum(array_column($data['programs'], $field));
            $faculty = $sum('faculty');
            $activities = $sum('activities');
            $total = compact('faculty', 'activities') + ['ratio' => $faculty > 0 && $activities !== null ? $activities / $faculty : null];
        }
        $data['totals'] = $total;

        return $data;
    }

    private function periodLabel(object $batch): string
    {
        return $batch->year_start.'/'.($batch->year_start + 1).' '.$batch->semester;
    }

    private function partners(?int $year): array
    {
        $batches = Schema::hasTable('community_partner_imports')
            ? DB::table('community_partner_imports')->orderByDesc('data_year')->get() : collect();
        $batch = $year === null ? $batches->first() : $batches->firstWhere('data_year', $year);
        $partners = $batch ? DB::table('community_partners')->where('import_id', $batch->id)->orderBy('source_row')
            ->get(['name', 'cluster', 'scale', 'area', 'joined_year'])->map(fn ($row) => (array) $row)->all() : [];
        $areas = ['Kab. Malang' => 0, 'Kota Malang' => 0, 'Lainnya' => 0];
        foreach ($partners as $partner) {
            $areas[in_array($partner['area'], ['Kab. Malang', 'Kota Malang'], true) ? $partner['area'] : 'Lainnya']++;
        }

        return ['partners' => $partners, 'clusters' => array_values(array_unique(array_column($partners, 'cluster'))),
            'scales' => array_values(array_unique(array_column($partners, 'scale'))),
            'areas' => array_map(fn ($label, $count) => compact('label', 'count'), array_keys($areas), array_values($areas)),
            'partnerYears' => $batches->pluck('data_year')->all(), 'selectedPartnerYear' => $batch?->data_year ?? $year,
            'partnerFilename' => $batch?->filename, 'partnersImported' => $batch !== null];
    }
}
