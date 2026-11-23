<?php

namespace App\Services\CommunityDevelopment;

use App\Services\ColumnMapping\ColumnMapperService;
use App\Services\Imports\ImportPolicy;
use Aspera\Spreadsheet\XLSX\Reader;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class CommunityDevelopmentWorkbook
{
    public const SHEETS_SEMESTER = ['rasio' => ['Rasio', 'pkm_ratio'], 'keterlibatan' => ['Keterlibatan', 'pkm_involvement']];
    public const SHEETS_PERIODIC = ['keterlibatan' => ['Keterlibatan', 'pkm_involvement']];

    public function __construct(private ColumnMapperService $mapper) {}

    private function getSheetsConfig(string $type): array
    {
        return $type === 'periodic' ? self::SHEETS_PERIODIC : self::SHEETS_SEMESTER;
    }

    /**
     * @param  array<string, string|null>|null  $selection  Sheet name per role.
     */
    public function preview(string $path, string $type = 'semester', ?array $selection = null): array
    {
        $sheetsConfig = $this->getSheetsConfig($type);
        $book = $this->sheets($path, $sheetsConfig, $selection, 35, true);
        $preview = [];
        foreach ($sheetsConfig as $key => [, $sheetType]) {
            ['name' => $sheet, 'rows' => $rows] = $book['sheets'][$key];
            $config = $this->mapper->getMappingConfig($sheetType);
            if (! $rows) {
                $preview[$key] = ['sheet' => $sheet, 'header_row' => 1, 'headers' => [], 'preview_data' => [],
                    'mapping_config' => $config, 'suggested_mapping' => []];

                continue;
            }
            [$headerRow] = $this->headerRow($rows, $sheetType);
            $headers = $rows[$headerRow];
            $preview[$key] = [
                'sheet' => $sheet, 'header_row' => $headerRow, 'headers' => $headers,
                'preview_data' => array_values(array_slice(array_filter($rows, fn ($number) => $number > $headerRow, ARRAY_FILTER_USE_KEY), 0, 5)),
                'mapping_config' => $config, 'suggested_mapping' => $this->mapper->suggestMapping($headers, $sheetType),
            ];
        }

        return ['success' => true, 'sheets' => $preview, 'available_sheets' => $book['names']];
    }

    /**
     * The row (within the first 30) whose cells match the most required fields, and that score.
     */
    private function headerRow(array $rows, string $type): array
    {
        $required = $this->mapper->getMappingConfig($type)['required'];
        $headerRow = array_key_first($rows);
        $best = -1;
        foreach (array_slice($rows, 0, 30, true) as $number => $cells) {
            $score = count(array_intersect_key($this->mapper->suggestMapping($cells, $type), $required));
            if ($score > $best) {
                $best = $score;
                $headerRow = $number;
            }
        }

        return [$headerRow, $best];
    }

    public function read(string $path, string $type, array $mappings, array $headerRows, ?array $selection = null): array
    {
        $sheetsConfig = $this->getSheetsConfig($type);
        $book = $this->sheets($path, $sheetsConfig, $selection);
        $sheets = array_map(fn ($sheet) => $sheet['rows'], $book['sheets']);
        $records = [];
        $resolved = [];
        $policy = new ImportPolicy('pkm', 'pkm');
        $duplicates = 0;
        $warnings = [];
        foreach ($sheetsConfig as $key => [$role, $sheetType]) {
            $sheetName = $book['sheets'][$key]['name'];
            $name = strcasecmp(trim($sheetName), $role) === 0 ? $role : "{$sheetName} ({$role})";
            $headerRow = $headerRows[$key] ?? null;
            if (! is_int($headerRow) || $headerRow < 1 || $headerRow > 1000 || ! isset($sheets[$key][$headerRow])) {
                $this->invalid("Baris header sheet {$name} tidak valid.", "header_rows.$key");
            }
            if (! isset($mappings[$key]) || ! is_array($mappings[$key])) {
                $this->invalid("Pemetaan sheet {$name} wajib dikonfirmasi.", "column_mapping.$key");
            }
            $headers = $sheets[$key][$headerRow];
            try {
                $mapping = $this->mapper->resolveMapping($headers, $sheetType, $mappings[$key]);
            } catch (ValidationException $exception) {
                $this->invalid("Sheet {$name}: ".implode(' ', array_merge(...array_values($exception->errors()))), "column_mapping.$key");
            }
            $resolved[$key] = $mapping;
            $records[$key] = [];
            foreach ($sheets[$key] as $number => $cells) {
                if ($number <= $headerRow) {
                    continue;
                }
                $record = [];
                foreach ($mapping as $field => $index) {
                    $value = $index === null ? '' : trim($cells[$index] ?? '');
                    $record[$field] = $value === '' ? null : $value;
                }
                foreach ($this->mapper->validateRow($record, $sheetType, $number, $mapping, $headers)['errors'] as $error) {
                    $policy->accept("Sheet {$name}: {$error}");
                }
                $identity = $key === 'rasio' ? trim($record['program'] ?? '') : strtoupper(trim($record['kode_dosen'] ?? ''));
                if ($identity === '') {
                    $policy->skip("Sheet {$name} baris {$number}: identitas prodi/kode dosen kosong.");

                    continue;
                }
                if (mb_strlen($identity) > ($key === 'rasio' ? 255 : 100)) {
                    $policy->skip("Sheet {$name} baris {$number}: identitas terlalu panjang.");

                    continue;
                }
                if ($key === 'rasio') {
                    $id = CommunityDevelopmentPrograms::key($identity);
                    $record = [
                        'program_key' => $id, 'source_program' => $identity, 'is_total' => $id === '_total',
                        'faculty' => $this->number($record['faculty'] ?? null, 'Jumlah FM', $name, $number, $policy, true),
                        'activities' => $this->number($record['activities'] ?? null, 'Jumlah Kegiatan', $name, $number, $policy, true),
                        'ratio' => $this->number($record['ratio'] ?? null, 'Rasio', $name, $number, $policy, max: 99999999.9999),
                    ];
                } else {
                    $id = $identity;
                    $record = [
                        'kode_dosen' => $identity,
                        'reported_activities' => $this->number($record['reported_activities'] ?? null, 'Jumlah kegiatan yang dilaporkan ke binusmaya', $name, $number, $policy, true),
                        'score' => $this->number($record['score'] ?? null, 'Skor KPI', $name, $number, $policy, max: 6),
                        'status' => $record['status'] ?? null,
                    ];
                }
                if (isset($records[$key][$id])) {
                    $first = $records[$key][$id];
                    unset($first['source_row']);
                    if ($first !== $record) {
                        $policy->skip("Sheet {$name} baris {$number}: {$identity} duplikat dengan nilai berbeda; data pertama dipakai.");
                    } else {
                        $duplicates++;
                    }

                    continue;
                }
                $records[$key][$id] = $record + ['source_row' => $number];
            }
            if ($key === 'rasio' && (! $records[$key] || ! array_filter($records[$key], fn ($row) => ! $row['is_total']))) {
                $this->invalid("Sheet {$name} tidak berisi data yang dapat diimpor. Data lama tetap tersimpan.");
            }
            if ($key === 'keterlibatan' && ! $records[$key]) {
                $this->invalid("Sheet {$name} tidak berisi data yang dapat diimpor. Data lama tetap tersimpan.");
            }
        }
        $codes = array_keys($records['keterlibatan'] ?? []);
        $known = Schema::hasTable('database_dosen_new')
            ? DB::table('database_dosen_new')->pluck('kode_dosen')->map(fn ($code) => strtoupper(trim($code)))->all() : [];
        $unmatched = array_values(array_diff($codes, $known));
        if ($unmatched) {
            $warnings[] = count($unmatched).' kode dosen belum ditemukan pada Modules/Dosen: '.implode(', ', $unmatched).'. Data kegiatan tetap disimpan dan akan terhubung setelah data dosen tersedia.';
        }

        return ['records' => $records, 'mapping' => $resolved, 'summary' => $policy->summary([
            'sheets' => array_map(fn ($sheet) => $sheet['name'], $book['sheets']),
            'selected' => count($records['keterlibatan'] ?? []), 'programs' => isset($records['rasio']) ? count(array_filter($records['rasio'], fn ($row) => ! $row['is_total'])) : 0,
            'duplicates' => $duplicates, 'matched' => count($codes) - count($unmatched), 'unmatched_codes' => $unmatched, 'warnings' => $warnings,
        ])];
    }

    public function import(string $path, string $type, array $params, array $mappings, array $headerRows, string $filename, ?array $selection = null): array
    {
        if ($type === 'semester') {
            $academicYear = $params['academic_year'] ?? '';
            $semester = $params['semester'] ?? '';
            if (! preg_match('/^(20\d{2}|2100)\/(20\d{2}|2101)$/', $academicYear, $parts) || (int) $parts[2] !== (int) $parts[1] + 1) {
                $this->invalid('Tahun akademik harus berurutan, misalnya 2025/2026.', 'academic_year');
            }
            if (! in_array($semester, ['Ganjil', 'Genap'], true)) {
                $this->invalid('Pilih semester Ganjil atau Genap.', 'period');
            }
        } elseif ($type === 'periodic') {
            $startDate = $params['start_date'] ?? '';
            $endDate = $params['end_date'] ?? '';
            if (! strtotime($startDate) || ! strtotime($endDate)) {
                $this->invalid('Rentang tanggal mulai dan selesai harus valid.', 'start_date');
            }
        } else {
            $this->invalid('Tipe import tidak valid.');
        }
        $parsed = $this->read($path, $type, $mappings, $headerRows, $selection);
        foreach (['community_development_imports', 'community_development_programs', 'community_development_lecturers'] as $table) {
            if (! Schema::hasTable($table)) {
                $this->invalid('Migrasi Community Development belum dijalankan.');
            }
        }

        return DB::transaction(function () use ($parsed, $type, $params, $headerRows, $filename) {
            if ($type === 'semester') {
                $parts = [];
                preg_match('/^(20\d{2}|2100)\/(20\d{2}|2101)$/', $params['academic_year'], $parts);
                $yearStart = (int) $parts[1];
                $semester = $params['semester'];
                $batch = DB::table('community_development_imports')->where('type', 'semester')->where('year_start', $yearStart)->where('semester', $semester)->lockForUpdate()->first();
                $values = ['filename' => $filename, 'column_mapping' => json_encode($parsed['mapping'], JSON_THROW_ON_ERROR),
                    'header_rows' => json_encode($headerRows, JSON_THROW_ON_ERROR), 'updated_at' => now(), 'type' => 'semester', 'year_start' => $yearStart, 'semester' => $semester, 'start_date' => null, 'end_date' => null];
            } else {
                $startDate = $params['start_date'];
                $endDate = $params['end_date'];
                $batch = DB::table('community_development_imports')->where('type', 'periodic')->where('start_date', $startDate)->where('end_date', $endDate)->lockForUpdate()->first();
                $values = ['filename' => $filename, 'column_mapping' => json_encode($parsed['mapping'], JSON_THROW_ON_ERROR),
                    'header_rows' => json_encode($headerRows, JSON_THROW_ON_ERROR), 'updated_at' => now(), 'type' => 'periodic', 'year_start' => null, 'semester' => null, 'start_date' => $startDate, 'end_date' => $endDate];
            }

            if ($batch) {
                $id = $batch->id;
                DB::table('community_development_imports')->where('id', $id)->update($values);
                DB::table('community_development_programs')->where('import_id', $id)->delete();
                DB::table('community_development_lecturers')->where('import_id', $id)->delete();
            } else {
                $id = DB::table('community_development_imports')->insertGetId($values + ['created_at' => now()]);
            }
            foreach (['rasio' => 'community_development_programs', 'keterlibatan' => 'community_development_lecturers'] as $key => $table) {
                if (! isset($parsed['records'][$key])) {
                    continue;
                }
                foreach (array_chunk(array_values($parsed['records'][$key]), 100) as $rows) {
                    DB::table($table)->insert(array_map(fn ($row) => $row + ['import_id' => $id], $rows));
                }
            }

            $periodLabel = $type === 'semester' ? $params['academic_year'].' '.$params['semester'] : 'Periode '.$params['start_date'].' s.d. '.$params['end_date'];

            return $parsed['summary'] + ['import_id' => $id, 'type' => $type,
                'period_label' => $periodLabel, 'replaced' => $batch !== null];
        });
    }

    private function number(?string $value, string $label, string $sheet, int $row, ImportPolicy $policy, bool $integer = false, float $max = 4294967295): int|float|null
    {
        if ($value === null) {
            return null;
        }
        $value = str_replace(',', '.', $value);
        if (! is_numeric($value) || ! is_finite((float) $value) || (float) $value < 0 || (float) $value > $max || ($integer && floor((float) $value) !== (float) $value)) {
            $policy->accept("Sheet {$sheet} baris {$row}: {$label} harus berupa angka ".($integer ? 'bulat ' : '')."antara 0 dan {$max}; nilai dikosongkan.");

            return null;
        }

        return $integer ? (int) $value : (float) $value;
    }

    /**
     * @return array{sheets: array<string, array{name: string, rows: array}>, names: list<string>}
     */
    private function sheets(string $path, array $sheetsConfig, ?array $selection = null, ?int $limit = null, bool $preview = false): array
    {
        $reader = new Reader;
        $names = [];
        $chosen = [];
        $rows = [];
        try {
            $reader->open($path);
            foreach ($reader->getSheets() as $index => $sheet) {
                $names[$index] = $sheet->getName();
            }
            foreach ($sheetsConfig as $key => [$role]) {
                $requested = $selection[$key] ?? null;
                if ($requested !== null) {
                    $index = is_string($requested) ? array_search($requested, $names, true) : false;
                    if ($index !== false) {
                        $chosen[$key] = $index;

                        continue;
                    }
                }
                foreach ($names as $index => $name) {
                    if (strcasecmp(trim($name), $role) === 0) {
                        $chosen[$key] = $index;
                        break;
                    }
                }
                if (! isset($chosen[$key])) {
                    $chosen[$key] = $preview && $names ? 0 : null;
                }
            }
            foreach ($sheetsConfig as $key => [$role]) {
                $index = $chosen[$key];
                if ($index === null || ! isset($names[$index])) {
                    if ($preview) {
                        $rows[$key] = ['name' => $role, 'rows' => []];

                        continue;
                    }
                    $this->invalid("Sheet {$role} tidak ditemukan pada file Excel.");
                }
                $sheetName = $names[$index];
                $reader->changeSheet($index);
                $sheetRows = [];
                $count = 0;
                foreach ($reader as $rowIndex => $row) {
                    if ($limit !== null && ++$count > $limit) {
                        break;
                    }
                    $sheetRows[$rowIndex] = $row;
                }
                $rows[$key] = ['name' => $sheetName, 'rows' => $sheetRows];
            }
        } catch (\Exception $exception) {
            if ($preview) {
                foreach ($sheetsConfig as $key => [$role]) {
                    if (! isset($rows[$key])) {
                        $rows[$key] = ['name' => $role, 'rows' => []];
                    }
                }
            } else {
                throw ValidationException::withMessages(['pkm' => 'File Excel tidak dapat dibaca: '.$exception->getMessage()]);
            }
        }

        return ['sheets' => $rows, 'names' => $names];
    }

    private function invalid(string $message, ?string $key = null): void
    {
        throw ValidationException::withMessages([$key ?? 'pkm' => $message]);
    }
}
