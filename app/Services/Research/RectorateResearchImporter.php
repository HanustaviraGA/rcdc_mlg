<?php

namespace App\Services\Research;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class RectorateResearchImporter
{
    public function __construct(private RectorateResearchReader $reader) {}

    public function import(string $path, string $filename, bool $dryRun = false, ?int $year = null, ?int $month = null): array
    {
        if (($year === null) !== ($month === null)) {
            throw ValidationException::withMessages(['month' => 'Tahun dan bulan laporan harus diisi bersama.']);
        }
        $year ??= (int) now()->year;
        $month ??= (int) now()->month;
        if ($year < 2000 || $year > 2100 || $month < 1 || $month > 12) {
            throw ValidationException::withMessages(['month' => 'Tahun/bulan laporan tidak valid.']);
        }
        $parsed = $this->reader->read($path);
        $summary = $parsed['summary'] + compact('year', 'month');
        $master = Schema::hasTable('database_dosen_new') ? 'database_dosen_new' : 'database_dosen';
        $known = Schema::hasTable($master) ? DB::table($master)->pluck('kode_dosen')->map(fn ($code) => strtoupper(trim($code)))->all() : [];
        $fm = array_filter($parsed['records'], fn ($row) => strtoupper($row['kategori_fm_eksternal_mahasiswa'] ?? '') === 'FM');
        $summary['unmatched_fm_codes'] = array_values(array_diff(array_unique(array_column($fm, 'kode_dosen_nim')), $known));
        if ($dryRun) {
            return $summary + ['dry_run' => true];
        }
        if (! Schema::hasColumn('research_imports', 'month') || ! Schema::hasColumn('rectorate_research', 'evidence')) {
            throw ValidationException::withMessages(['research_file' => 'Migrasi import riset rectorate belum dijalankan.']);
        }
        $sha256 = hash_file('sha256', $path);

        return DB::transaction(function () use ($parsed, $summary, $filename, $sha256, $year, $month) {
            $snapshot = compact('year', 'month');
            // Lock the monthly snapshot before checking or replacing its contents.
            DB::table('research_imports')->upsert([$snapshot + [
                'filename' => basename($filename), 'sha256' => $sha256, 'sheet' => $summary['sheet'], 'campus' => RectorateResearchReader::CAMPUS,
                'summary' => json_encode($summary, JSON_THROW_ON_ERROR), 'created_at' => now(), 'updated_at' => now(),
            ]], ['year', 'month'], ['year']);
            $batch = DB::table('research_imports')->where($snapshot)->lockForUpdate()->first();
            if ($batch->sha256 === $sha256 && DB::table('rectorate_research')->where('research_import_id', $batch->id)->exists()) {
                return json_decode($batch->summary, true) + ['import_id' => $batch->id, 'already_imported' => true];
            }
            DB::table('research_imports')->where('id', $batch->id)->update([
                'filename' => basename($filename), 'sha256' => $sha256, 'sheet' => $summary['sheet'],
                'summary' => json_encode($summary, JSON_THROW_ON_ERROR), 'updated_at' => now(),
            ]);
            DB::table('rectorate_research')->where('research_import_id', $batch->id)->delete();
            foreach (array_chunk($parsed['records'], 50) as $chunk) {
                DB::table('rectorate_research')->insert(array_map(fn ($row) => $row + [
                    'id_research' => md5($batch->id.'|'.$row['row_key']), 'research_import_id' => $batch->id,
                    'created_at' => now(), 'updated_at' => now(),
                ], $chunk));
            }

            return $summary + ['import_id' => $batch->id, 'already_imported' => false];
        });
    }
}
