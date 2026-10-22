<?php

namespace App\Services\Lecturers;

use App\Models\DataDosen;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LecturerSnapshots
{
    public function batch(int $year, ?int $month = null): ?object
    {
        if (! Schema::hasTable('lecturer_imports')) {
            return null;
        }

        return DB::table('lecturer_imports')->where('year', $year)
            ->when($month !== null, fn ($query) => $query->where('month', '<=', $month))
            ->orderByDesc('month')->first();
    }

    public function query(?int $year = null, ?int $month = null): Builder
    {
        $year ??= (int) now()->year;
        $batch = $this->batch($year, $month);
        if ($year === (int) now()->year && $month === null) {
            return DataDosen::query()->currentRoster();
        }
        if (! Schema::hasTable('lecturer_snapshot_rows')) {
            return DataDosen::query()->whereRaw('1 = 0');
        }

        return DataDosen::query()->from('lecturer_snapshot_rows as database_dosen_new')
            ->select('database_dosen_new.*')->where('lecturer_import_id', $batch?->id ?? 0);
    }

    public function rows(int $year, ?int $month = null): Collection
    {
        // Reporting evidence is retained even when the live public profile is archived.
        return $this->query($year, $month)->withTrashed()->get()->keyBy('kode_dosen');
    }

    public function years(): Collection
    {
        return (Schema::hasTable('lecturer_imports') ? DB::table('lecturer_imports')->pluck('year') : collect())
            ->push((int) now()->year)->unique()->sortDesc()->values();
    }
}
