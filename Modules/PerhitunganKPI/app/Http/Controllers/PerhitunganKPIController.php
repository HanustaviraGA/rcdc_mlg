<?php

namespace Modules\PerhitunganKPI\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Publications\ImportedLecturerKpi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PerhitunganKPIController extends Controller
{
    public function index(ImportedLecturerKpi $kpi)
    {
        abort_unless(Auth::check(), 403);
        $rows = $kpi->rows();

        return loadPage('perhitungankpi::index', [
            'rows' => $rows,
            'years' => $rows->pluck('year')->filter()->unique()->sortDesc()->values(),
            'programs' => $rows->pluck('program')->unique()->sort()->values(),
        ]);
    }

    public function init_table(Request $request, ImportedLecturerKpi $kpi)
    {
        abort_unless(Auth::check(), 403);
        $filters = $request->validate([
            'year' => ['nullable', 'integer', 'between:2000,2100'],
            'month' => ['nullable', 'integer', 'between:1,12'],
            'prodi' => ['nullable', 'string', 'max:255'],
            'search' => ['nullable', 'string', 'max:255'],
        ]);
        $rows = $kpi->filter($kpi->rows(), $filters);

        return response()->json([
            'html' => view('perhitungankpi::rows', compact('rows'))->render(),
            'count' => $rows->count(),
            'lecturers' => $rows->pluck('code')->unique()->count(),
        ]);
    }
}
