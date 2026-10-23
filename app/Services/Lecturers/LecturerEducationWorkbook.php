<?php

namespace App\Services\Lecturers;

use App\Services\Imports\ImportPolicy;
use Aspera\Spreadsheet\XLSX\Reader;
use DateTimeInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class LecturerEducationWorkbook
{
    public const SHEET = 'Detail Jenjang Pendidikan';

    public const FIELDS = ['kode_dosen' => 'Kode Dosen', 'status_pendidikan' => 'Status Pendidikan'];

    public function preview(string $path): array
    {
        [$sheet, $rows] = $this->rows($path, 35);
        $headerRow = array_key_first($rows);
        foreach (array_slice($rows, 0, 30, true) as $number => $cells) {
            if (in_array('kode dosen', array_map($this->normalize(...), $cells), true)) {
                $headerRow = $number;
                break;
            }
        }
        $headers = $rows[$headerRow];
        $mapping = [];
        foreach (['kode_dosen' => 'kode dosen', 'status_pendidikan' => 'pendidikan'] as $field => $label) {
            $matches = array_keys(array_map($this->normalize(...), $headers), $label, true);
            if (count($matches) === 1) {
                $mapping[$field] = $matches[0];
            }
        }

        return [
            'sheet' => $sheet, 'header_row' => $headerRow, 'headers' => $headers,
            'preview_data' => array_values(array_slice(array_filter($rows, fn ($number) => $number > $headerRow, ARRAY_FILTER_USE_KEY), 0, 5)),
            'mapping_config' => ['required' => self::FIELDS, 'optional' => (object) []],
            'suggested_mapping' => $mapping,
        ];
    }

    public function read(string $path, array $mapping, int $headerRow): array
    {
        [$sheet, $rows] = $this->rows($path);
        if ($headerRow < 1 || $headerRow > 1000 || ! isset($rows[$headerRow])) {
            $this->invalid('Baris header pemetaan tidak valid.', 'header_row');
        }
        $errors = [];
        foreach ($mapping as $field => $index) {
            if (! array_key_exists($field, self::FIELDS)) {
                $errors["column_mapping.$field"] = 'Kolom sistem tidak dikenal.';
            } elseif (! is_int($index) || $index < 0 || ! array_key_exists($index, $rows[$headerRow])) {
                $errors["column_mapping.$field"] = 'Sumber kolom tidak valid.';
            }
        }
        foreach (self::FIELDS as $field => $label) {
            if (! isset($mapping[$field]) || $mapping[$field] === '') {
                $errors["column_mapping.$field"] = "Kolom {$label} wajib dipetakan.";
            }
        }
        if (count(array_unique(array_filter($mapping, is_int(...)))) !== count(array_filter($mapping, is_int(...)))) {
            $errors['column_mapping'] = 'Setiap kolom file hanya boleh dipetakan ke satu kolom tujuan.';
        }
        if ($errors) {
            throw ValidationException::withMessages($errors);
        }
        $records = [];
        $duplicates = 0;
        $policy = new ImportPolicy('dosen_status_pendidikan', 'dosen');
        foreach ($rows as $number => $cells) {
            if ($number <= $headerRow) {
                continue;
            }
            $code = strtoupper(trim($cells[$mapping['kode_dosen']] ?? ''));
            if ($code === '') {
                $policy->skip("Baris {$number}: Kode Dosen wajib diisi.");

                continue;
            }
            $status = trim($cells[$mapping['status_pendidikan']] ?? '');
            $status = $status === '' ? null : $status;
            if (array_key_exists($code, $records)) {
                if ($records[$code] !== $status) {
                    $policy->skip("Baris {$number}: kode dosen {$code} duplikat dengan status pendidikan berbeda; data pertama dipakai.");

                    continue;
                }
                $duplicates++;

                continue;
            }
            $records[$code] = $status;
        }
        if (! $records) {
            $this->invalid('Sheet tidak berisi data status pendidikan.');
        }

        return ['records' => $records, 'summary' => $policy->summary([
            'sheet' => $sheet, 'selected' => count($records), 'duplicates' => $duplicates,
        ])];
    }

    public function import(string $path, int $year, int $month, array $mapping, int $headerRow): array
    {
        if ($year < 2000 || $year > 2100 || $month < 1 || $month > 12) {
            $this->invalid('Tahun dan bulan tidak valid.', 'month');
        }
        $parsed = $this->read($path, $mapping, $headerRow);
        foreach (['database_dosen_new', 'lecturer_snapshot_rows'] as $table) {
            if (! Schema::hasColumn($table, 'status_pendidikan')) {
                $this->invalid('Migrasi status pendidikan dosen belum dijalankan.');
            }
        }

        return DB::transaction(function () use ($parsed, $year, $month) {
            $batch = DB::table('lecturer_imports')->where('year', $year)->where('month', $month)->lockForUpdate()->first();
            if (! $batch) {
                $this->invalid('Unggah Data Center untuk tahun dan bulan terpilih terlebih dahulu.', 'month');
            }
            $rows = DB::table('lecturer_snapshot_rows')->where('lecturer_import_id', $batch->id)->get()->keyBy('kode_dosen');
            $updateMaster = $year === (int) now()->year && ! DB::table('lecturer_imports')->where('year', $year)->where('month', '>', $month)->exists();
            $updated = 0;
            $unchanged = 0;
            $unmatched = [];
            foreach ($parsed['records'] as $code => $status) {
                $row = $rows->get($code);
                if (! $row) {
                    $unmatched[] = $code;

                    continue;
                }
                $row->status_pendidikan === $status ? $unchanged++ : $updated++;
                DB::table('lecturer_snapshot_rows')->where('id', $row->id)->update([
                    'status_pendidikan' => $status, 'updated_at' => now(),
                ]);
                if ($updateMaster) {
                    DB::table('database_dosen_new')->where('kode_dosen', $code)->update([
                        'status_pendidikan' => $status, 'updated_at' => now(),
                    ]);
                }
            }
            if ($updated + $unchanged === 0) {
                $this->invalid('Tidak ada Kode Dosen pada file yang cocok dengan Data Center periode terpilih.');
            }

            return $parsed['summary'] + [
                'year' => $year, 'month' => $month, 'updated' => $updated, 'unchanged' => $unchanged,
                'unmatched_codes' => $unmatched, 'updated_current_master' => $updateMaster,
            ];
        });
    }

    private function rows(string $path, ?int $limit = null): array
    {
        $reader = new Reader;
        try {
            $reader->open($path);
            $selected = null;
            foreach ($reader->getSheets() as $index => $sheet) {
                if ($this->normalize($sheet->getName()) === $this->normalize(self::SHEET)) {
                    $selected = ['index' => $index, 'name' => $sheet->getName()];
                    break;
                }
            }
            if (! $selected) {
                $this->invalid('Sheet '.self::SHEET.' tidak ditemukan.');
            }
            $reader->changeSheet($selected['index']);
            $rows = [];
            foreach ($reader as $number => $cells) {
                $cells = array_map(fn ($cell) => $cell instanceof DateTimeInterface ? $cell->format('Y-m-d') : trim((string) $cell), $cells);
                if (array_filter($cells, fn ($cell) => $cell !== '')) {
                    $rows[(int) $number] = array_values($cells);
                    if ($limit !== null && count($rows) >= $limit) {
                        break;
                    }
                }
            }
            if (! $rows) {
                $this->invalid('Sheet '.self::SHEET.' kosong.');
            }

            return [$selected['name'], $rows];
        } finally {
            $reader->close();
        }
    }

    private function normalize(string $value): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/u', ' ', str_replace("\xC2\xA0", ' ', $value))));
    }

    private function invalid(string $message, string $field = 'dosen'): never
    {
        throw ValidationException::withMessages([$field => $message.' Data sebelumnya tetap tersimpan.']);
    }
}
