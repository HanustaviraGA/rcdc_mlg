<?php

namespace App\Services\Research;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RectorateResearchRepository
{
    public function activeRows(): Collection
    {
        if (! Schema::hasColumn('rectorate_research', 'research_import_id')) {
            return collect();
        }
        $latest = DB::table('rectorate_research')->whereNotNull('research_import_id')
            ->whereRaw('LOWER(TRIM(lokasi_kampus)) = ?', [strtolower(RectorateResearchReader::CAMPUS)])
            ->select('budget_year', DB::raw('MAX(research_import_id) as batch_id'))->groupBy('budget_year')->get();
        if ($latest->isEmpty()) {
            return collect();
        }

        return DB::table('rectorate_research')
            ->whereRaw('LOWER(TRIM(lokasi_kampus)) = ?', [strtolower(RectorateResearchReader::CAMPUS)])
            ->where(function ($query) use ($latest) {
                foreach ($latest as $snapshot) {
                    $query->orWhere(fn ($q) => $q->where('budget_year', $snapshot->budget_year)->where('research_import_id', $snapshot->batch_id));
                }
            })->orderBy('source_row')->get();
    }
}
