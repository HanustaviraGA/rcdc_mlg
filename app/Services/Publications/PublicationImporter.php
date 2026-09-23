<?php

namespace App\Services\Publications;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PublicationImporter
{
    public function __construct(private RawPublicationReader $reader, private WorkbookKpiReader $kpiReader) {}

    public function import(string $path, string $filename, int $year, int $month, int $period, bool $dryRun = false): array
    {
        if ($year < 2000 || $year > 2100 || $month < 1 || $month > 12 || $period !== (int) ceil($month / 3)) {
            throw ValidationException::withMessages(['period' => 'Tahun/bulan tidak valid atau quarter tidak sesuai bulan snapshot.']);
        }
        $parsed = $this->reader->read($path, $year);
        $kpi = $this->kpiReader->read($path, $parsed['records']);
        $summary = $parsed['summary'] + ['year' => $year, 'month' => $month, 'period' => $period];
        $summary['kpi'] = $kpi['summary'];
        $summary['warnings'] = array_merge($summary['warnings'], $kpi['summary']['warnings']);
        $metrics = PublicationWorkbookMetrics::summarize(collect($parsed['records']));
        $summary['workbook_totals'] = array_intersect_key($metrics, array_flip(['titles', 'first_author', 'rectorate_scopus', 'rectorate_non_scopus']));
        $known = DB::table('database_dosen')->pluck('kode_dosen')->all();
        $summary['unmatched_codes'] = array_values(array_diff(array_unique(array_merge(array_column($parsed['records'], 'kode_dosen'), array_column($kpi['entries'], 'kode_dosen'))), $known));
        if ($dryRun) {
            return $summary;
        }
        if (! Schema::hasTable('publication_imports')) {
            throw ValidationException::withMessages(['rectorate' => 'Migrasi pelacakan import publikasi belum dijalankan.']);
        }
        if ($kpi['entries'] && ! Schema::hasTable('publication_kpi_entries')) {
            throw ValidationException::withMessages(['rectorate' => 'Migrasi data KPI workbook belum dijalankan.']);
        }

        return DB::transaction(function () use ($parsed, $kpi, $summary, $filename, $path, $year, $month, $period) {
            $snapshot = compact('year', 'month', 'period');
            // A unique snapshot row serializes concurrent reimports of the same month.
            DB::table('publication_imports')->upsert([$snapshot + [
                'filename' => basename($filename), 'sha256' => hash_file('sha256', $path),
                'summary' => json_encode($summary, JSON_THROW_ON_ERROR), 'created_at' => now(), 'updated_at' => now(),
            ]], ['year', 'month', 'period'], ['filename', 'sha256', 'summary', 'updated_at']);
            $batch = DB::table('publication_imports')->where($snapshot)->lockForUpdate()->first();
            if (Schema::hasTable('publication_kpi_entries')) {
                DB::table('publication_kpi_entries')->where('publication_import_id', $batch->id)->delete();
                foreach (array_chunk($kpi['entries'], 100) as $chunk) {
                    DB::table('publication_kpi_entries')->insert(array_map(fn ($row) => $row + [
                        'publication_import_id' => $batch->id, 'created_at' => now(), 'updated_at' => now(),
                    ], $chunk));
                }
            }
            DB::table('rectorate_dosen')->where($snapshot)->delete();
            foreach (array_chunk($parsed['records'], 100) as $chunk) {
                DB::table('rectorate_dosen')->insert(array_map(fn ($row) => $row + $snapshot + [
                    'id_rectorate' => md5((string) Str::uuid()), 'publication_import_id' => $batch->id,
                    'created_at' => now(), 'updated_at' => now(),
                ], $chunk));
            }

            return $summary + ['import_id' => $batch->id];
        });
    }
}
