<?php

namespace App\Http\Controllers;

use App\Services\Research\RectorateResearchImporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ResearchImportController extends Controller
{
    public function index()
    {
        abort_unless(Auth::check(), 403);

        $imports = Schema::hasTable('research_imports') ? DB::table('research_imports')->orderByDesc('id')->limit(10)->get() : collect();

        return view('dashboard.research_import', compact('imports'));
    }

    public function store(Request $request, RectorateResearchImporter $importer)
    {
        abort_unless(Auth::check(), 403);

        $request->validate(['research_file' => ['required', 'file', 'mimes:xlsx', 'max:20480']]);
        $file = $request->file('research_file');
        $summary = $importer->import($file->getRealPath(), $file->getClientOriginalName());

        return redirect()->route('research-import.index')->with('research_summary', $summary);
    }
}
