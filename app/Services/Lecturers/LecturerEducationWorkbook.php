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

    public function __construct(private LecturerEducationStatuses $statuses) {}

    public function preview(string $path, ?string $sheet = null): array
    {
        [$sheet, $rows, $sheets] = $this->rows($path, 35, $sheet, true);
        if (! $rows) {
            // Keep the sheet list so another sheet can be chosen.
            return ['sheet' => $sheet, 'sheets' => $sheets, 'header_row' => 1, 'headers' => [], 'preview_data' => [],
                'mapping_config' => ['required' => self::FIELDS, 'optional' => (object) []], 'suggested_mapping' => []];
        }
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
            'sheet' => $sheet, 'sheets' => $sheets, 'header_row' => $headerRow, 'headers' => $headers,
            'preview_data' => array_values(array_slice(array_filter($rows, fn ($number) => $number > $headerRow, ARRAY_FILTER_USE_KEY), 0, 5)),
            'mapping_config' => ['required' => self::FIELDS, 'optional' => (object) []],
            'suggested_mapping' => $mapping,
        ];
    }

    public function read(string $path, array $mapping, int $headerRow, ?string $sheet = null): array
    {
        [$sheet, $rows] = $this->rows($path, null, $sheet);
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

    public function import(string $path, int $year, int $month, array $mapping, int $headerRow, ?string $sheet = null): array
    {
        if ($year < 2000 || $year > 2100 || $month < 1 || $month > 12) {
            $this->invalid('Tahun dan bulan tidak valid.', 'month');
        }
        $parsed = $this->read($path, $mapping, $headerRow, $sheet);
        foreach (['database_dosen_new', 'lecturer_snapshot_rows'] as $table) {
            if (! Schema::hasColumn($table, 'status_pendidikan')) {
                $this->invalid('Migrasi status pendidikan dosen belum dijalankan.');
            }
        }
        if (! $this->statuses->available()) {
            $this->invalid('Migrasi status pendidikan dosen belum dijalankan.');
        }

        return DB::transaction(function () use ($parsed, $year, $month) {
            $batch = DB::table('lecturer_imports')->where('year', $year)->where('month', $month)->lockForUpdate()->first();
            if (! $batch) {
                $this->invalid('Unggah Data Center untuk tahun dan bulan terpilih terlebih dahulu.', 'month');
            }
            $rows = DB::table('lecturer_snapshot_rows')->where('lecturer_import_id', $batch->id)->get()->keyBy('kode_dosen');
            $updateMaster = $year === (int) now()->year && ! DB::table('lecturer_imports')->where('year', $year)->where('month', '>', $month)->exists();
            // What the period shows now, including a status carried over from an earlier upload.
            $current = $this->statuses->asOf($year, $month);
            $updated = 0;
            $unchanged = 0;
            $unmatched = [];
            $matched = [];
            foreach ($parsed['records'] as $code => $status) {
                $row = $rows->get($code);
                if (! $row) {
                    $unmatched[] = $code;

                    continue;
                }
                ($current->has($code) ? $current->get($code) : $row->status_pendidikan) === $status ? $unchanged++ : $updated++;
                $matched[$code] = $status;
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
            // The rows above are replaced by the next Data Center upload; this copy is not.
            $this->statuses->store($matched, $year, $month);

            return $parsed['summary'] + [
                'year' => $year, 'month' => $month, 'updated' => $updated, 'unchanged' => $unchanged,
                'unmatched_codes' => $unmatched, 'updated_current_master' => $updateMaster,
            ];
        });
    }

    /**
     * A chosen sheet must exist. Without a choice, use Detail Jenjang Pendidikan; a
     * preview falls back to the first sheet (and may be empty) so another can be picked.
     */
    private function rows(string $path, ?int $limit = null, ?string $requested = null, bool $preview = false): array
    {
        $reader = new Reader;
        try {
            $reader->open($path);
            $names = [];
            foreach ($reader->getSheets() as $index => $sheet) {
                $names[$index] = $sheet->getName();
            }
            if ($requested !== null) {
                $index = array_search($requested, $names, true);
                if ($index === false) {
                    $this->invalid("Sheet {$requested} tidak ditemukan pada file.");
                }
            } else {
                $index = array_search($this->normalize(self::SHEET), array_map($this->normalize(...), $names), true);
                if ($index === false && $preview && $names) {
                    $index = array_key_first($names);
                }
                if ($index === false) {
                    $this->invalid('Sheet '.self::SHEET.' tidak ditemukan. Pilih sheet status pendidikan pada pratinjau.');
                }
            }
            $reader->changeSheet($index);
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
            if (! $rows && ! $preview) {
                $this->invalid('Sheet '.$names[$index].' kosong.');
            }

            return [$names[$index], $rows, array_values($names)];
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
