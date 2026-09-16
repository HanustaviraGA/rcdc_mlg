<?php

namespace App\Services\Research;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class RectorateResearchImporter
{
    public function __construct(private RectorateResearchReader $reader) {}

    public function import(string $path, string $filename, bool $dryRun = false): array
    {
        $parsed = $this->reader->read($path);
        $summary = $parsed['summary'];
        $known = Schema::hasTable('database_dosen') ? DB::table('database_dosen')->pluck('kode_dosen')->all() : [];
        $fm = array_filter($parsed['records'], fn ($row) => strtoupper($row['kategori_fm_eksternal_mahasiswa'] ?? '') === 'FM');
        $summary['unmatched_fm_codes'] = array_values(array_diff(array_unique(array_column($fm, 'kode_dosen_nim')), $known));
        if ($dryRun) {
            return $summary + ['dry_run' => true];
        }
        if (! Schema::hasColumn('rectorate_research', 'research_import_id')) {
            throw ValidationException::withMessages(['research_file' => 'Migrasi import riset rectorate belum dijalankan.']);
        }
        $sha256 = hash_file('sha256', $path);

        return DB::transaction(function () use ($parsed, $summary, $filename, $sha256) {
            // A no-op upsert locks the unique file hash, including concurrent duplicate uploads.
            DB::table('research_imports')->upsert([[
                'filename' => basename($filename), 'sha256' => $sha256, 'sheet' => 'Detail', 'campus' => RectorateResearchReader::CAMPUS,
                'summary' => json_encode($summary, JSON_THROW_ON_ERROR), 'created_at' => now(), 'updated_at' => now(),
            ]], ['sha256'], ['sha256']);
            $batch = DB::table('research_imports')->where('sha256', $sha256)->lockForUpdate()->first();
            if (DB::table('rectorate_research')->where('research_import_id', $batch->id)->exists()) {
                return json_decode($batch->summary, true) + ['import_id' => $batch->id, 'already_imported' => true];
            }
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
