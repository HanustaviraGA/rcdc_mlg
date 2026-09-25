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
        $snapshots = DB::table('rectorate_research')
            ->join('research_imports', 'research_imports.id', '=', 'rectorate_research.research_import_id')
            ->whereRaw('LOWER(TRIM(lokasi_kampus)) = ?', [strtolower(RectorateResearchReader::CAMPUS)])
            ->select('budget_year', 'research_import_id as batch_id')->distinct();
        if (Schema::hasColumn('research_imports', 'month')) {
            $snapshots->addSelect('research_imports.year', 'research_imports.month')
                ->orderByDesc('research_imports.year')->orderByDesc('research_imports.month');
        }
        $latest = $snapshots->orderByDesc('batch_id')->get()->unique('budget_year');
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
