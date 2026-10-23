<?php

namespace App\Services\Lecturers;

use App\Models\DataDosen;
use App\Services\Imports\ImportPolicy;
use Aspera\Spreadsheet\XLSX\Reader;
use DateTimeInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class LecturerWorkbook
{
    // public const HEADERS = [
    //     'kode_dosen' => 'Kode Dosen', 'fakultas_internal' => 'Fakultas Internal',
    //     'nama_gugus_binaan' => 'Program Studi', 'nama_program' => 'Nama Program',
    //     'lokasi' => 'Lokasi', 'campus' => 'Campus', 'nama_gugus_binaan_eksternal' => 'Nama Gugus Binaan Eksternal',
    //     'acad_career' => 'Acad Career', 'nama_dosen' => 'Nama Dosen', 'tipe' => 'Tipe',
    //     'nama_tipe_dosen_detail' => 'Nama Tipe Dosen Detail', 'status' => 'Status',
    //     'effdate_dosen_cuti' => 'Effdate Dosen Cuti', 'remun' => 'Remun', 'homebase_remun' => 'Homebase Remun',
    //     'jenis_registrasi' => 'Jenis Registrasi', 'nomor_nidn_nupn' => 'Nomor Nidn/Nupn',
    //     'university_registered_nidn' => 'University Registered NIDN', 'pendidikan' => 'Pendidikan',
    //     'alumni' => 'Alumni', 'jurusan' => 'Jurusan', 'jja' => 'JJA', 'tmt_jja' => 'Tmt JJA',
    //     'university_registered_jja' => 'University Registered JJA', 'nomor_sk_jja' => 'Nomor SK JJA',
    //     'jka' => 'JKA', 'tmt_jka' => 'Tmt JKA', 'toefl' => 'TOEFL', 'status_serdos' => 'Status Serdos',
    //     'jenis_kelamin' => 'Jenis Kelamin', 'tanggal_lahir' => 'Tanggal Lahir', 'usia' => 'Usia',
    //     'agama' => 'Agama', 'alamat' => 'Alamat', 'no_telp' => 'No Telp', 'no_hp' => 'No HP',
    //     'no_hp_2' => 'No HP2', 'email_1' => 'Email 1', 'email_2' => 'Email 2',
    //     'tgl_mulai_mengajar' => 'Tgl Mulai Mengajar', 'kewarganegaraan' => 'Kewarganegaraan',
    //     'bn_id' => 'Binusian ID', 'nama_kelompok_rumpun_ilmu' => 'Nama Kelompok Rumpun Ilmu',
    //     'nama_rumpun_ilmu' => 'Nama Rumpun Ilmu', 'tipe_faculty' => 'Tipe Faculty',
    //     'tax_status' => 'Tax Status', 'status_pernikahan' => 'Status Pernikahan', 'note' => 'NOTE',
    // ];

    public const HEADERS = [
        'kode_dosen' => 'Kode Dosen', 'nama_gugus_binaan' => 'Program Studi', 'nama_program' => 'Nama Program',
        'lokasi' => 'Lokasi', 'campus' => 'Campus', 'nama_dosen' => 'Nama Dosen', 'tipe' => 'Tipe',
        'nama_tipe_dosen_detail' => 'Nama Tipe Dosen Detail', 'status' => 'Status',
        'effdate_dosen_cuti' => 'Effdate Dosen Cuti', 'jenis_registrasi' => 'Jenis Registrasi', 'nomor_nidn_nupn' => 'Nomor NIDN/NUPN',
        'pendidikan' => 'Pendidikan', 'jja' => 'JJA', 'tmt_jja' => 'Tmt JJA',
        'jka' => 'JKA', 'tmt_jka' => 'Tmt JKA', 'jenis_kelamin' => 'Jenis Kelamin', 'tanggal_lahir' => 'Tanggal Lahir', 'usia' => 'Usia',
        'no_telp' => 'No Telp', 'no_hp' => 'No HP',
        'no_hp_2' => 'No HP2', 'email_1' => 'Email 1', 'email_2' => 'Email 2',
        'bn_id' => 'Binusian ID', 'nama_kelompok_rumpun_ilmu' => 'Nama Kelompok Rumpun Ilmu',
        'nama_rumpun_ilmu' => 'Nama Rumpun Ilmu', 'tipe_faculty' => 'Tipe Faculty',
        'note' => 'NOTE',
    ];

    private const REQUIRED_FIELDS = ['kode_dosen', 'nama_dosen', 'nama_gugus_binaan', 'jja', 'tipe_faculty', 'status', 'pendidikan'];

    public function preview(string $path): array
    {
        $reader = new Reader;
        try {
            $reader->open($path);
            $sheet = $this->selectSheet($reader);
            $reader->changeSheet($sheet['index']);
            $candidates = [];
            foreach ($reader as $number => $row) {
                $row = array_map($this->cell(...), $row);
                if (! array_filter($row, fn ($value) => $value !== '')) {
                    continue;
                }
                $candidates[] = ['number' => (int) $number, 'cells' => $row];
                if (count($candidates) >= 30) {
                    break;
                }
            }
            if (! $candidates) {
                $this->invalid('Sheet dosen kosong.');
            }
            $header = $candidates[0];
            foreach ($candidates as $candidate) {
                if (in_array('kode dosen', array_map($this->normalize(...), $candidate['cells']), true)) {
                    $header = $candidate;
                    break;
                }
            }
            $mapping = [];
            foreach (self::HEADERS as $field => $label) {
                $matches = array_keys(array_map($this->normalize(...), $header['cells']), $this->normalize($label), true);
                if (count($matches) === 1) {
                    $mapping[$field] = $matches[0];
                }
            }
            $required = array_intersect_key(self::HEADERS, array_flip(self::REQUIRED_FIELDS));

            return [
                'sheet' => $sheet['name'], 'header_row' => $header['number'], 'headers' => array_values($header['cells']),
                'preview_data' => array_values(array_map(fn ($row) => array_values($row['cells']), array_slice(array_filter($candidates, fn ($row) => $row['number'] > $header['number']), 0, 5))),
                'mapping_config' => ['required' => $required, 'optional' => array_diff_key(self::HEADERS, $required)],
                'suggested_mapping' => $mapping,
            ];
        } finally {
            $reader->close();
        }
    }

    private function cell(mixed $value): string
    {
        return $value instanceof DateTimeInterface ? $value->format('Y-m-d') : trim((string) $value);
    }

    private function selectSheet(Reader $reader): array
    {
        $sheets = [];
        foreach ($reader->getSheets() as $index => $sheet) {
            $sheets[$this->normalize($sheet->getName())] = ['index' => $index, 'name' => $sheet->getName()];
        }
        $selected = $sheets['malang'] ?? $sheets['fm malang'] ?? $sheets['fm binus malang'] ?? null;
        if ($selected === null) {
            $this->invalid('Sheet Malang, FM Malang, atau FM BINUS Malang tidak ditemukan.');
        }

        return $selected;
    }

    private function userMap(array $mapping, array $headers): array
    {
        // An unselected optional field is absent, not an invalid column index.
        foreach ($mapping as $field => $index) {
            if (array_key_exists($field, self::HEADERS) && ! in_array($field, self::REQUIRED_FIELDS, true)
                && ($index === null || $index === '')) {
                unset($mapping[$field]);
            }
        }
        $errors = [];
        foreach ($mapping as $field => $index) {
            if (! array_key_exists($field, self::HEADERS)) {
                $errors["column_mapping.$field"] = 'Kolom sistem tidak dikenal.';
            } elseif (! is_int($index) || $index < 0 || ! array_key_exists($index, $headers)) {
                $errors["column_mapping.$field"] = 'Sumber kolom tidak valid.';
            }
        }
        $indices = array_filter($mapping, is_int(...));
        if (count(array_unique($indices)) !== count($indices)) {
            $errors['column_mapping'] = 'Setiap kolom file hanya boleh dipetakan ke satu kolom tujuan.';
        }
        foreach (self::REQUIRED_FIELDS as $field) {
            if (! isset($mapping[$field]) || $mapping[$field] === '') {
                $errors["column_mapping.$field"] = 'Kolom '.self::HEADERS[$field].' wajib dipetakan.';
            }
        }
        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        return $mapping;
    }

    public function read(string $path, ?array $columnMapping = null, ?int $headerRow = null): array
    {
        if ($columnMapping !== null && ($headerRow === null || $headerRow < 1 || $headerRow > 1000)) {
            throw ValidationException::withMessages(['header_row' => 'Baris header pemetaan tidak valid.']);
        }
        $reader = new Reader;
        $records = [];
        $map = null;
        $headers = [];
        $duplicates = 0;
        $policy = new ImportPolicy('dosen_data_center', 'dosen');
        try {
            $reader->open($path);
            $sheet = $this->selectSheet($reader);
            $reader->changeSheet($sheet['index']);
            foreach ($reader as $number => $row) {
                $row = array_map(fn ($value) => $value instanceof DateTimeInterface ? $value->format('Y-m-d') : trim((string) $value), $row);
                if (! array_filter($row, fn ($value) => $value !== '')) {
                    continue;
                }
                if ($map === null) {
                    $normalized = array_map($this->normalize(...), $row);
                    if ($columnMapping !== null ? (int) $number !== $headerRow : ! in_array('kode dosen', $normalized, true)) {
                        continue;
                    }
                    $headers = $row;
                    if ($columnMapping !== null) {
                        $map = $this->userMap($columnMapping, $row);

                        continue;
                    }
                    $map = [];
                    foreach (self::HEADERS as $field => $label) {
                        $matches = array_keys($normalized, $this->normalize($label), true);
                        if (count($matches) > 1) {
                            $this->invalid("Kolom {$label} duplikat.");
                        }
                        if ($matches) {
                            $map[$field] = $matches[0];
                        }
                    }
                    if (! isset($map['nama_dosen'])) {
                        $this->invalid('Kolom Nama Dosen tidak ditemukan.');
                    }
                    if ($policy->force) {
                        $map = $this->userMap($map, $headers);
                    }

                    continue;
                }
                $record = [];
                foreach ($map as $field => $index) {
                    $record[$field] = ($row[$index] ?? '') === '' ? null : $row[$index];
                }
                $code = strtoupper(trim($record['kode_dosen'] ?? ''));
                if ($code === '') {
                    $policy->skip("Baris {$number}: Kode Dosen kosong.");

                    continue;
                }
                if ($record['nama_dosen'] === null) {
                    $policy->accept("Baris {$number}: Nama Dosen kosong.");
                }
                $record['kode_dosen'] = $code;
                if (isset($records[$code])) {
                    if ($records[$code] !== $record) {
                        $policy->skip("Baris {$number}: kode dosen {$code} duplikat dengan isi berbeda; data pertama dipakai.");

                        continue;
                    }
                    $duplicates++;

                    continue;
                }
                $records[$code] = $record;
            }
        } finally {
            $reader->close();
        }
        if (! $records) {
            $this->invalid('Sheet dosen tidak berisi data atau header Kode Dosen tidak ditemukan.');
        }
        $mapped = array_intersect_key(self::HEADERS, $map);
        $unknown = array_values(array_filter($headers, fn ($header, $index) => $header !== '' && ! in_array($index, $map, true), ARRAY_FILTER_USE_BOTH));

        return ['records' => array_values($records), 'summary' => $policy->summary([
            'sheet' => $sheet['name'], 'selected' => count($records), 'duplicates' => $duplicates,
            'headers' => $headers, 'mapped_columns' => count($mapped), 'unknown_columns' => $unknown,
            'unmapped_columns' => array_values(array_diff_key(self::HEADERS, $map)),
        ])];
    }

    public function import(string $path, ?int $year = null, ?int $month = null, ?array $columnMapping = null, ?int $headerRow = null, ?string $filename = null): array
    {
        if ($year === null && $month === null && Schema::hasTable('lecturer_imports')) {
            $year = (int) now()->year;
            $month = (int) now()->month;
        }
        if (($year === null) !== ($month === null) || ($year !== null && ($year < 2000 || $year > 2100 || $month < 1 || $month > 12))) {
            $this->invalid('Tahun dan bulan update tidak valid.');
        }
        $parsed = $this->read($path, $columnMapping, $headerRow);

        return DB::transaction(function () use ($parsed, $year, $month, $columnMapping, $filename) {
            $created = 0;
            $removed = 0;
            $updateMaster = $year === null;
            if ($year !== null) {
                if (! Schema::hasTable('lecturer_imports')) {
                    $this->invalid('Migrasi snapshot dosen belum dijalankan.');
                }
                $snapshot = compact('year', 'month');
                DB::table('lecturer_imports')->upsert([$snapshot + [
                    'filename' => basename($filename ?? 'dosen.xlsx'), 'sheet' => $parsed['summary']['sheet'],
                    'column_mapping' => json_encode($columnMapping), 'created_at' => now(), 'updated_at' => now(),
                ]], ['year', 'month'], ['filename', 'sheet', 'column_mapping', 'updated_at']);
                $batch = DB::table('lecturer_imports')->where($snapshot)->lockForUpdate()->first();
                $existing = DB::table('lecturer_snapshot_rows')->where('lecturer_import_id', $batch->id)->pluck('kode_dosen')->all();
                $hasEducationStatus = Schema::hasColumn('lecturer_snapshot_rows', 'status_pendidikan');
                // Education status comes from its own import for this same period.
                $educationStatuses = $hasEducationStatus
                    ? DB::table('lecturer_snapshot_rows')->where('lecturer_import_id', $batch->id)->pluck('status_pendidikan', 'kode_dosen')
                    : collect();
                $removed = count(array_diff($existing, array_column($parsed['records'], 'kode_dosen')));
                // A validated workbook replaces the complete period; unmapped fields stay null.
                DB::table('lecturer_snapshot_rows')->where('lecturer_import_id', $batch->id)->delete();
                foreach ($parsed['records'] as $record) {
                    $education = $hasEducationStatus ? ['status_pendidikan' => $educationStatuses->get($record['kode_dosen'])] : [];
                    DB::table('lecturer_snapshot_rows')->insert($record + $education + [
                        'lecturer_import_id' => $batch->id, 'created_at' => now(), 'updated_at' => now(),
                    ]);
                    $created += (int) ! in_array($record['kode_dosen'], $existing, true);
                }
                $updateMaster = $year === (int) now()->year && ! DB::table('lecturer_imports')->where('year', $year)->where('month', '>', $month)->exists();
            }
            if ($updateMaster) {
                $masterCreated = 0;
                $masterRecords = $year === null ? $parsed['records'] : DB::table('lecturer_snapshot_rows')
                    ->where('lecturer_import_id', $batch->id)->get()
                    ->map(fn ($row) => array_intersect_key((array) $row, self::HEADERS + ['status_pendidikan' => 'Status Pendidikan']))->all();
                foreach ($masterRecords as $record) {
                    // Never restore deleted profiles or reset global visibility on import.
                    $model = DataDosen::withTrashed()->updateOrCreate(['kode_dosen' => $record['kode_dosen']], $record);
                    $masterCreated += (int) $model->wasRecentlyCreated;
                }
                if ($year === null) {
                    $created = $masterCreated;
                }
            }

            return $parsed['summary'] + ['year' => $year, 'month' => $month, 'created' => $created,
                'updated' => count($parsed['records']) - $created, 'removed' => $removed, 'updated_current_master' => $updateMaster];
        });
    }

    private function normalize(string $value): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/u', ' ', str_replace("\xC2\xA0", ' ', $value))));
    }

    private function invalid(string $message): never
    {
        throw ValidationException::withMessages(['dosen' => $message.' Data sebelumnya tetap tersimpan.']);
    }
}
