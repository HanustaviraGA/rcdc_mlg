<?php

namespace Modules\ImportRectorate\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Publications\PublicationImporter;
use App\Services\Publications\StudentPublicationImporter;
use App\Services\Research\RectorateResearchImporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ImportRectorateController extends Controller
{
    public function index()
    {
        abort_unless(Auth::check(), 403);

        return loadPage('importrectorate::index', $this->history());
    }

    public function store(Request $request, RectorateResearchImporter $importer)
    {
        abort_unless(Auth::check(), 403);

        $validated = $request->validate([
            'research_file' => ['required_without_all:fm_file,mhs_file', 'nullable', 'file', 'mimes:xlsx', 'max:20480'],
            'fm_file' => ['nullable', 'file', 'mimes:xlsx', 'max:20480'],
            'mhs_file' => ['nullable', 'file', 'mimes:xlsx', 'max:20480'],
            'year' => ['required', 'integer', 'between:2000,2100'],
            'month' => ['required', 'integer', 'between:1,12'],
        ]);
        $summaries = DB::transaction(function () use ($request, $validated, $importer) {
            $year = (int) $validated['year'];
            $month = (int) $validated['month'];
            $results = [];
            foreach (['fm_file', 'mhs_file', 'research_file'] as $type) {
                if (! $request->hasFile($type)) {
                    continue;
                }
                $file = $request->file($type);
                $results[$type] = match ($type) {
                    'fm_file' => app(PublicationImporter::class)->import($file->getRealPath(), $file->getClientOriginalName(), $year, $month, (int) ceil($month / 3)),
                    'mhs_file' => app(StudentPublicationImporter::class)->import($file->getRealPath(), $file->getClientOriginalName(), $year, $month),
                    default => $importer->import($file->getRealPath(), $file->getClientOriginalName(), year: $year, month: $month),
                };
            }

            return $results;
        });

        if ($request->expectsJson()) {
            return response()->json([
                'summaries' => $summaries,
                'summary_html' => view('importrectorate::summary', compact('summaries'))->render(),
                'history_html' => view('importrectorate::history', $this->history())->render(),
            ]);
        }

        return redirect('/dashboard/importrectorate')->with('monthly_summaries', $summaries)
            ->with('research_summary', $summaries['research_file'] ?? null);
    }

    private function history(): array
    {
        $monthly = collect();
        $legacyResearch = false;
        foreach (['fm' => 'publication_imports', 'mhs' => 'student_publication_imports', 'hibah' => 'research_imports'] as $type => $table) {
            $batches = Schema::hasTable($table) ? DB::table($table)->orderBy('updated_at')->get() : collect();
            foreach ($batches as $batch) {
                if (! ($batch->year ?? null) || ! ($batch->month ?? null)) {
                    $legacyResearch = $legacyResearch || $type === 'hibah';

                    continue;
                }
                $key = sprintf('%04d-%02d', $batch->year, $batch->month);
                $monthly[$key] = array_merge($monthly[$key] ?? [], [$type => $batch->filename]);
            }
        }

        return ['monthly' => $monthly->sortKeysDesc(), 'legacyResearch' => $legacyResearch];
    }
}
