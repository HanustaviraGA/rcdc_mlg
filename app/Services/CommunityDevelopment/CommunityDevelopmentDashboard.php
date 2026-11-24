<?php

namespace App\Services\CommunityDevelopment;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CommunityDevelopmentDashboard
{
    public function __construct(private CommunityDevelopmentDemo $demo) {}

    public function data(?int $period = null, ?int $partnerYear = null, ?string $periodicStart = null, ?string $periodicEnd = null): array
    {
        $data = array_replace($this->demo->data(), $this->partners($partnerYear));

        // Semester batches for Section 2 (Rekap Data PkM Dosen)
        $batches = Schema::hasTable('community_development_imports')
            ? DB::table('community_development_imports')->where(fn ($q) => $q->where('type', 'semester')->orWhereNull('type'))->orderByDesc('year_start')->orderBy('semester')->get() : collect();
        $batches = $batches->sortByDesc(fn ($batch) => $batch->year_start * 2 + ($batch->semester === 'Genap' ? 1 : 0))->values();

        // Periodic batches for Section 1 (Skor KPI PkM Dosen)
        $periodicBatches = Schema::hasTable('community_development_imports')
            ? DB::table('community_development_imports')->where('type', 'periodic')->orderByDesc('start_date')->get() : collect();

        $data += [
            'periods' => $batches->map(fn ($batch) => ['id' => $batch->id, 'label' => $this->periodLabel($batch)])->all(),
            'selectedPeriod' => null, 'periodLabel' => 'Data contoh', 'unassignedLecturers' => [], 'filename' => null,
            'periodicPeriods' => $periodicBatches->map(fn ($batch) => ['id' => $batch->id, 'label' => 'Rentang '.$this->formatDateId($batch->start_date).' s.d. '.$this->formatDateId($batch->end_date), 'filename' => $batch->filename])->all(),
            'selectedPeriodicStart' => $periodicStart, 'selectedPeriodicEnd' => $periodicEnd,
            'periodicPeriodLabel' => 'Belum ada data periodik', 'periodicPrograms' => [], 'periodicUnassignedLecturers' => [],
        ];

        // Load Periodic batch for Section 1 by date range
        $periodicBatch = null;
        if ($periodicStart && $periodicEnd) {
            $periodicBatch = $periodicBatches->first(fn ($b) => $b->start_date <= $periodicEnd && $b->end_date >= $periodicStart)
                ?? $periodicBatches->firstWhere('start_date', $periodicStart);
        }
        if (! $periodicBatch) {
            $periodicBatch = $periodicBatches->first();
        }

        if ($periodicBatch) {
            $data['selectedPeriodicStart'] = $periodicBatch->start_date;
            $data['selectedPeriodicEnd'] = $periodicBatch->end_date;
            $data['periodicPeriodLabel'] = 'Rentang '.$this->formatDateId($periodicBatch->start_date).' s.d. '.$this->formatDateId($periodicBatch->end_date).' ('.$periodicBatch->filename.')';
            $periodicLecturersData = $this->loadBatchLecturers($periodicBatch);
            $data['periodicPrograms'] = $periodicLecturersData['programs'];
            $data['periodicUnassignedLecturers'] = $periodicLecturersData['unassigned'];
        }

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

        $batchLecturersData = $this->loadBatchLecturers($batch);
        $data['programs'] = $batchLecturersData['programs'];
        $data['unassignedLecturers'] = $batchLecturersData['unassigned'];

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
        }
        if ($total === null) {
            $sum = fn ($field) => in_array(null, array_column($data['programs'], $field), true) ? null : array_sum(array_column($data['programs'], $field));
            $faculty = $sum('faculty');
            $activities = $sum('activities');
            $total = compact('faculty', 'activities') + ['ratio' => $faculty > 0 && $activities !== null ? $activities / $faculty : null];
        }
        $data['totals'] = $total;

        return $data;
    }

    private function loadBatchLecturers(object $batch): array
    {
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
        $programs = [];
        foreach (DB::table('community_development_programs')->where('import_id', $batch->id)->orderBy('source_row')->get() as $row) {
            $faculty = $row->faculty === null ? null : (int) $row->faculty;
            $activities = $row->activities === null ? null : (int) $row->activities;
            $values = compact('faculty', 'activities') + ['ratio' => $row->ratio === null
                ? ($faculty > 0 && $activities !== null ? $activities / $faculty : null) : (float) $row->ratio];
            if ($row->is_total) {
                continue;
            }
            $programs[] = $values + ['key' => $row->program_key, 'code' => $row->source_program,
                'label' => CommunityDevelopmentPrograms::label($row->program_key, $row->source_program),
                'lecturers' => $lecturers[$row->program_key] ?? []];
            unset($lecturers[$row->program_key]);
        }
        $unassigned = array_merge([], ...array_values($lecturers));

        return ['programs' => $programs, 'unassigned' => $unassigned];
    }

    private function periodLabel(object $batch): string
    {
        return $batch->year_start.'/'.($batch->year_start + 1).' '.$batch->semester;
    }

    private function formatDateId(?string $dateStr): string
    {
        if (! $dateStr) {
            return '—';
        }
        try {
            $date = new \DateTime($dateStr);
            $months = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
            ];
            $day = $date->format('j');
            $month = $months[(int) $date->format('n')] ?? $date->format('F');
            $year = $date->format('Y');

            return "{$day} {$month} {$year}";
        } catch (\Exception $e) {
            return $dateStr;
        }
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
