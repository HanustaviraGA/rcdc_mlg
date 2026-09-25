<?php

namespace App\Http\Controllers;

use App\Services\Research\RectorateResearchImporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\ImportRectorate\Http\Controllers\ImportRectorateController;

class ResearchImportController extends Controller
{
    public function index()
    {
        abort_unless(Auth::check(), 403);

        return redirect('/dashboard/importrectorate');
    }

    public function store(Request $request, RectorateResearchImporter $importer)
    {
        return app(ImportRectorateController::class)->store($request, $importer);
    }
}
