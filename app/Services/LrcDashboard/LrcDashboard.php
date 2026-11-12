<?php

namespace App\Services\LrcDashboard;

use App\Models\DataDosen;
use App\Services\Lecturers\LecturerEducationStatuses;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class LrcDashboard
{
    private const EDUCATION_LABELS = ['S2', 'S3', 'On Going S3'];

    private const RANK_LABELS = ['TP', 'AA', 'L', 'LK', 'GB'];

    private const RANK_CHART_LABELS = ['TP (Tenaga Pengajar)', 'AA (Asisten Ahli)', 'L (Lektor)', 'LK (Lektor Kepala)', 'GB (Guru Besar)'];

    private const AGE_LABELS = ['20-40 tahun', '41-54 tahun', '55 tahun', '56-64 tahun', '65 tahun', '> 65 tahun'];

    private const FACULTY_LABELS = ['FM', 'FMSCS', 'FMSTR'];

    public function data(array $selected = []): array
    {
        $lecturers = DataDosen::query()->currentRoster()->get();
        app(LecturerEducationStatuses::class)->apply($lecturers);
        $rows = $lecturers->map(fn ($row) => $this->row($row));
        $options = $this->options($rows);
        $selected = $this->selectedFilters($selected, $options);
        $filtered = $rows->filter(fn (array $row) => $this->matches($row, $selected))->values();
        $religions = array_keys($filtered->countBy('agama')->sortKeys()->all());
        // Chart key => [row key, row values in chart order].
        $groups = [
            'pendidikan' => ['education', self::EDUCATION_LABELS],
            'jja' => ['rank', self::RANK_LABELS],
            'usia' => ['ageLabel', self::AGE_LABELS],
            'serdos' => ['serdos', ['Lulus', 'Belum Lulus']],
            'nuptk' => ['nuptk', ['Sudah punya NUPTK', 'Belum punya NUPTK']],
            'tipeDosen' => ['facultyType', ['Functional Faculty', 'Professional Faculty']],
            'agama' => ['agama', $religions],
        ];

        return [
            'filters' => $options + ['selected' => $selected],
            'total' => $filtered->count(),
            'sourceTotal' => $rows->count(),
            'rankProgression' => array_map(fn (int $index) => [
                'current' => self::RANK_LABELS[$index], 'next' => self::RANK_LABELS[$index + 1] ?? null,
                'nextLabel' => self::RANK_CHART_LABELS[$index + 1] ?? null,
            ], array_keys(self::RANK_LABELS)),
            'charts' => [
                'pendidikan' => $this->chart(self::EDUCATION_LABELS, $this->educationCounts($filtered)),
                'jja' => $this->chart(self::RANK_CHART_LABELS, $this->fixedCounts($filtered, 'rank', self::RANK_LABELS)),
                'usia' => $this->chart(self::AGE_LABELS, $this->fixedCounts($filtered, 'ageLabel', self::AGE_LABELS)),
                'serdos' => $this->chart(['Lulus', 'Belum Lulus'], $this->fixedCounts($filtered, 'serdos', ['Lulus', 'Belum Lulus'])),
                'nuptk' => $this->chart(['Sudah punya NUPTK', 'Belum punya NUPTK'], $this->fixedCounts($filtered, 'nuptk', ['Sudah punya NUPTK', 'Belum punya NUPTK'])),
                'tipeDosen' => $this->chart(['Functional Faculty', 'Professional Faculty'], $this->fixedCounts($filtered, 'facultyType', ['Functional Faculty', 'Professional Faculty'])),
                'agama' => $this->chart($religions, $this->fixedCounts($filtered, 'agama', $religions)),
                'fakultas' => $this->chart(self::FACULTY_LABELS, $this->fixedCounts($filtered, 'tipe', self::FACULTY_LABELS)),
            ],
            // Lecturers behind each bar/slice, aligned with the chart labels by index.
            'chartDetails' => array_map(fn (array $group) => array_map(
                fn ($value) => $this->lecturers($filtered->whereStrict($group[0], $value), $group[0] === 'rank'),
                $group[1]
            ), $groups),
            'facultyDetails' => $this->facultyDetails($filtered),
            'unclassified' => [
                'education' => $filtered->filter(fn (array $row) => ! in_array($row['education'], self::EDUCATION_LABELS, true))->count(),
                'rank' => $filtered->filter(fn (array $row) => ! in_array($row['rank'], self::RANK_LABELS, true))->count(),
                'age' => $filtered->whereNull('ageLabel')->count(),
            ],
        ];
    }

    private function row(object $row): array
    {
        $rank = $this->rank($row->jja ?? null);
        $facultyType = $this->facultyType($row->tipe_faculty ?? null);
        $faculty = $this->value($row->fakultas_internal ?? null);
        $registrationNumber = $this->value($row->nomor_nidn_nupn ?? null);
        $hasNuptk = $this->isDataValue($registrationNumber);

        return [
            'code' => $this->value($row->kode_dosen ?? null),
            'name' => $this->value($row->nama_dosen ?? null) ?: 'Tanpa nama',
            'faculty' => $faculty,
            // Komposisi Fakultas groups by Data Center `tipe` (FM/FMSCS/FMSTR), then by `tipe_faculty`.
            'tipe' => strtoupper((string) $this->value($row->tipe ?? null)),
            'facultyType' => $facultyType,
            'facultyDetail' => $this->value($row->tipe_faculty ?? null) ?: 'Tidak diketahui',
            'rank' => $rank,
            'roadmapKenaikanJja' => $this->value($row->roadmap_kenaikan_jja ?? null),
            'roadmapJjaRank' => $this->rank($row->roadmap_kenaikan_jja ?? null),
            'eligibleKenaikanJja' => $this->value($row->eligible_kenaikan_jja ?? null),
            'eligibleJjaYear' => isset($row->eligible_kenaikan_jja) ? (int) substr((string) $row->eligible_kenaikan_jja, 0, 4) : null,
            'education' => $this->education($row->pendidikan ?? null, $row->status_pendidikan ?? null),
            'roadmapS3' => isset($row->roadmap_s3) ? (int) $row->roadmap_s3 : null,
            'proyeksiLulusS3' => isset($row->proyeksi_lulus_s3) ? (int) $row->proyeksi_lulus_s3 : null,
            'ageLabel' => $this->ageLabel($row->usia ?? null, $row->tanggal_lahir ?? null),
            'serdos' => $this->serdos($row->status_serdos ?? null),
            'nuptk' => $hasNuptk ? 'Sudah punya NUPTK' : 'Belum punya NUPTK',
            'agama' => $this->value($row->agama ?? null) ?: 'Tidak diketahui',
            'gugus' => $this->value($row->nama_gugus_binaan ?? null),
            'program' => $this->value($row->nama_program ?? null),
            'status' => $this->value($row->status ?? null),
        ];
    }

    private function options(Collection $rows): array
    {
        return [
            'fakultas' => $this->optionsFor($rows, 'faculty'),
            'gugusBinaan' => $this->optionsFor($rows, 'gugus'),
            'program' => $this->optionsFor($rows, 'program'),
            'statusDosen' => $this->optionsFor($rows, 'status'),
        ];
    }

    private function selectedFilters(array $selected, array $options): array
    {
        return [
            'fakultas' => $this->selectedValue($selected['fakultas'] ?? null, $options['fakultas']),
            'gugusBinaan' => $this->selectedValue($selected['gugusBinaan'] ?? null, $options['gugusBinaan']),
            'program' => $this->selectedValue($selected['program'] ?? null, $options['program']),
            'statusDosen' => $this->selectedValue($selected['statusDosen'] ?? null, $options['statusDosen']),
        ];
    }

    private function selectedValue(?string $value, array $options): ?string
    {
        return $value !== null && in_array($value, $options, true) ? $value : null;
    }

    private function matches(array $row, array $selected): bool
    {
        return ($selected['fakultas'] === null || $row['faculty'] === $selected['fakultas'])
            && ($selected['gugusBinaan'] === null || $row['gugus'] === $selected['gugusBinaan'])
            && ($selected['program'] === null || $row['program'] === $selected['program'])
            && ($selected['statusDosen'] === null || $row['status'] === $selected['statusDosen']);
    }

    private function optionsFor(Collection $rows, string $key): array
    {
        return $rows->pluck($key)->filter(fn ($value) => $value !== null && $value !== '')->unique()->sort()->values()->all();
    }

    private function educationCounts(Collection $rows): array
    {
        $counts = array_fill_keys(self::EDUCATION_LABELS, 0);
        foreach ($rows as $row) {
            if (isset($counts[$row['education']])) {
                $counts[$row['education']]++;
            }
        }

        return array_values($counts);
    }

    private function fixedCounts(Collection $rows, string $key, array $labels): array
    {
        $counts = array_fill_keys($labels, 0);
        foreach ($rows as $row) {
            if (array_key_exists($row[$key], $counts)) {
                $counts[$row[$key]]++;
            }
        }

        return array_values($counts);
    }

    private function chart(array $labels, array $data): array
    {
        return ['labels' => array_values($labels), 'data' => array_map('intval', array_values($data))];
    }

    private function facultyDetails(Collection $rows): array
    {
        $result = [];
        foreach (self::FACULTY_LABELS as $faculty) {
            $facultyRows = $rows->where('tipe', $faculty)->values();
            $types = $facultyRows->groupBy('facultyDetail')->map(function (Collection $typeRows, string $label) {
                return ['label' => $label, 'count' => $typeRows->count(), 'lecturers' => $this->lecturers($typeRows)];
            })->sortKeys()->values()->all();
            $result[$faculty] = ['label' => $faculty, 'count' => $facultyRows->count(), 'types' => $types];
        }

        return $result;
    }

    /**
     * The fields shown in a lecturer list, sorted by name.
     */
    private function lecturers(Collection $rows, bool $rankPlanning = false): array
    {
        return $rows->sortBy('name')->map(fn (array $row) => [
            'code' => $row['code'], 'name' => $row['name'], 'gugus' => $row['gugus'], 'program' => $row['program'],
            ...($rankPlanning ? [
                'roadmapKenaikanJja' => $row['roadmapKenaikanJja'], 'roadmapJjaRank' => $row['roadmapJjaRank'],
                'eligibleKenaikanJja' => $row['eligibleKenaikanJja'], 'eligibleJjaYear' => $row['eligibleJjaYear'],
            ] : []),
            ...($row['education'] === 'S2' ? ['roadmapS3' => $row['roadmapS3'], 'proyeksiLulusS3' => $row['proyeksiLulusS3']] : []),
            ...($row['education'] === 'On Going S3' ? ['proyeksiLulusS3' => $row['proyeksiLulusS3']] : []),
        ])->values()->all();
    }

    private function rank(?string $value): ?string
    {
        $value = strtoupper(trim($value ?? ''));
        foreach (['LEKTOR KEPALA' => 'LK', 'ASISTEN AHLI' => 'AA', 'GURU BESAR' => 'GB', 'LEKTOR' => 'L', 'TENAGA PENGAJAR' => 'TP'] as $label => $code) {
            if (str_starts_with($value, $label)) {
                return $code;
            }
        }

        return preg_match('/^(LK|AA|GB|TP|L)(?:\d|\b)/', $value, $match) ? $match[1] : null;
    }

    private function facultyType(?string $value): ?string
    {
        $value = trim($value ?? '');
        if (stripos($value, 'Functional') === 0) {
            return 'Functional Faculty';
        }
        if (stripos($value, 'Professional') === 0) {
            return 'Professional Faculty';
        }

        return null;
    }

    private function education(?string $education, ?string $status): ?string
    {
        $normalize = fn (?string $value) => preg_replace('/[\s-]+/u', '', strtoupper(trim($value ?? '')));
        $status = $normalize($status);
        if ($status === 'S2') {
            return 'S2';
        }
        if ($status === 'S3') {
            return 'S3';
        }
        if ($status === 'ONGOINGS3') {
            return 'On Going S3';
        }
        $education = $normalize($education);

        return match ($education) {
            'S2' => 'S2', 'S3' => 'S3', 'ONGOINGS3' => 'On Going S3', default => null,
        };
    }

    private function ageLabel(mixed $age, mixed $birthDate): ?string
    {
        preg_match('/\b(\d{1,3})\b/', (string) $age, $match);
        $years = isset($match[1]) ? (int) $match[1] : null;
        if ($years === null && $birthDate) {
            try {
                $birth = Carbon::parse($birthDate);
                $years = $birth->year <= 1901 ? null : $birth->diffInYears(now());
            } catch (\Throwable) {
                $years = null;
            }
        }
        if ($years === null) {
            return null;
        }

        return match (true) {
            $years >= 20 && $years <= 40 => '20-40 tahun',
            $years >= 41 && $years <= 54 => '41-54 tahun',
            $years === 55 => '55 tahun',
            $years >= 56 && $years <= 64 => '56-64 tahun',
            $years === 65 => '65 tahun',
            $years > 65 => '> 65 tahun',
            default => null,
        };
    }

    private function serdos(?string $value): string
    {
        $value = strtolower(trim($value ?? ''));

        return str_contains($value, 'lulus') && ! str_contains($value, 'belum') ? 'Lulus' : 'Belum Lulus';
    }

    private function value(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function isDataValue(?string $value): bool
    {
        return $value !== null && ! in_array(strtoupper(trim($value)), ['', '-', 'N/A', 'NA', 'NULL', '0'], true);
    }
}
