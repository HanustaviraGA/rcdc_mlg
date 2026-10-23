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
    public const SHEETS = ['rasio' => ['Rasio', 'pkm_ratio'], 'keterlibatan' => ['Keterlibatan', 'pkm_involvement']];

    public function __construct(private ColumnMapperService $mapper) {}

    public function preview(string $path): array
    {
        $sheets = $this->sheets($path, 35);
        $preview = [];
        foreach (self::SHEETS as $key => [$name, $type]) {
            $rows = $sheets[$key];
            $headerRow = array_key_first($rows);
            $best = -1;
            $config = $this->mapper->getMappingConfig($type);
            foreach (array_slice($rows, 0, 30, true) as $number => $cells) {
                $mapping = $this->mapper->suggestMapping($cells, $type);
                $score = count(array_intersect_key($mapping, $config['required']));
                if ($score > $best) {
                    $best = $score;
                    $headerRow = $number;
                }
            }
            $headers = $rows[$headerRow];
            $preview[$key] = [
                'sheet' => $name, 'header_row' => $headerRow, 'headers' => $headers,
                'preview_data' => array_values(array_slice(array_filter($rows, fn ($number) => $number > $headerRow, ARRAY_FILTER_USE_KEY), 0, 5)),
                'mapping_config' => $config, 'suggested_mapping' => $this->mapper->suggestMapping($headers, $type),
            ];
        }

        return ['success' => true, 'sheets' => $preview];
    }

    public function read(string $path, array $mappings, array $headerRows): array
    {
        $sheets = $this->sheets($path);
        $records = [];
        $resolved = [];
        $policy = new ImportPolicy('pkm', 'pkm');
        $duplicates = 0;
        $warnings = [];
        foreach (self::SHEETS as $key => [$name, $type]) {
            $headerRow = $headerRows[$key] ?? null;
            if (! is_int($headerRow) || $headerRow < 1 || $headerRow > 1000 || ! isset($sheets[$key][$headerRow])) {
                $this->invalid("Baris header sheet {$name} tidak valid.", "header_rows.$key");
            }
            if (! isset($mappings[$key]) || ! is_array($mappings[$key])) {
                $this->invalid("Pemetaan sheet {$name} wajib dikonfirmasi.", "column_mapping.$key");
            }
            $headers = $sheets[$key][$headerRow];
            try {
                $mapping = $this->mapper->resolveMapping($headers, $type, $mappings[$key]);
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
                foreach ($this->mapper->validateRow($record, $type, $number, $mapping, $headers)['errors'] as $error) {
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
            if (! $records[$key] || ($key === 'rasio' && ! array_filter($records[$key], fn ($row) => ! $row['is_total']))) {
                $this->invalid("Sheet {$name} tidak berisi data yang dapat diimpor. Data lama tetap tersimpan.");
            }
        }
        $codes = array_keys($records['keterlibatan']);
        $known = Schema::hasTable('database_dosen_new')
            ? DB::table('database_dosen_new')->pluck('kode_dosen')->map(fn ($code) => strtoupper(trim($code)))->all() : [];
        $unmatched = array_values(array_diff($codes, $known));
        if ($unmatched) {
            $warnings[] = count($unmatched).' kode dosen belum ditemukan pada Modules/Dosen: '.implode(', ', $unmatched).'. Data kegiatan tetap disimpan dan akan terhubung setelah data dosen tersedia.';
        }

        return ['records' => $records, 'mapping' => $resolved, 'summary' => $policy->summary([
            'selected' => count($records['keterlibatan']), 'programs' => count(array_filter($records['rasio'], fn ($row) => ! $row['is_total'])),
            'duplicates' => $duplicates, 'matched' => count($codes) - count($unmatched), 'unmatched_codes' => $unmatched, 'warnings' => $warnings,
        ])];
    }

    public function import(string $path, string $academicYear, string $semester, array $mappings, array $headerRows, string $filename): array
    {
        if (! preg_match('/^(20\d{2}|2100)\/(20\d{2}|2101)$/', $academicYear, $parts) || (int) $parts[2] !== (int) $parts[1] + 1) {
            $this->invalid('Tahun akademik harus berurutan, misalnya 2025/2026.', 'academic_year');
        }
        if (! in_array($semester, ['Ganjil', 'Genap'], true)) {
            $this->invalid('Pilih semester Ganjil atau Genap.', 'period');
        }
        $parsed = $this->read($path, $mappings, $headerRows);
        foreach (['community_development_imports', 'community_development_programs', 'community_development_lecturers'] as $table) {
            if (! Schema::hasTable($table)) {
                $this->invalid('Migrasi Community Development belum dijalankan.');
            }
        }

        return DB::transaction(function () use ($parsed, $academicYear, $parts, $semester, $headerRows, $filename) {
            $batch = DB::table('community_development_imports')->where('year_start', (int) $parts[1])->where('semester', $semester)->lockForUpdate()->first();
            $values = ['filename' => $filename, 'column_mapping' => json_encode($parsed['mapping'], JSON_THROW_ON_ERROR),
                'header_rows' => json_encode($headerRows, JSON_THROW_ON_ERROR), 'updated_at' => now()];
            if ($batch) {
                $id = $batch->id;
                DB::table('community_development_imports')->where('id', $id)->update($values);
                DB::table('community_development_programs')->where('import_id', $id)->delete();
                DB::table('community_development_lecturers')->where('import_id', $id)->delete();
            } else {
                $id = DB::table('community_development_imports')->insertGetId($values + ['year_start' => (int) $parts[1], 'semester' => $semester, 'created_at' => now()]);
            }
            foreach (['rasio' => 'community_development_programs', 'keterlibatan' => 'community_development_lecturers'] as $key => $table) {
                foreach (array_chunk(array_values($parsed['records'][$key]), 100) as $rows) {
                    DB::table($table)->insert(array_map(fn ($row) => $row + ['import_id' => $id], $rows));
                }
            }

            return $parsed['summary'] + ['import_id' => $id, 'academic_year' => $academicYear, 'period' => $semester,
                'period_label' => $academicYear.' '.$semester, 'replaced' => $batch !== null];
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

    private function sheets(string $path, ?int $limit = null): array
    {
        $reader = new Reader;
        $result = [];
        try {
            $reader->open($path);
            foreach ($reader->getSheets() as $index => $sheet) {
                $key = strtolower(trim($sheet->getName()));
                if (! isset(self::SHEETS[$key])) {
                    continue;
                }
                if (isset($result[$key])) {
                    $this->invalid('Nama sheet duplikat: '.$sheet->getName());
                }
                $reader->changeSheet($index);
                $result[$key] = [];
                foreach ($reader as $number => $cells) {
                    $cells = array_map(fn ($value) => trim((string) $value), $cells);
                    if (! array_filter($cells, fn ($value) => $value !== '')) {
                        continue;
                    }
                    $result[$key][(int) $number] = $cells;
                    if ($limit !== null && count($result[$key]) >= $limit) {
                        break;
                    }
                }
            }
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable) {
            $this->invalid('File Excel tidak dapat dibaca. Pastikan formatnya .xlsx yang valid.');
        } finally {
            $reader->close();
        }
        foreach (self::SHEETS as $key => [$name]) {
            if (empty($result[$key])) {
                $this->invalid("Sheet {$name} wajib tersedia dan berisi data.");
            }
        }

        return $result;
    }

    private function invalid(string $message, string $field = 'pkm'): never
    {
        throw ValidationException::withMessages([$field => $message]);
    }
}
