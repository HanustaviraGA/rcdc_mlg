<?php

namespace App\Http\Controllers;

use App\Services\LrcDashboard\LrcDashboard;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class LrcDashboardController extends Controller
{
    public function index(Request $request, LrcDashboard $dashboard): View
    {
        $validated = $request->validate([
            'fakultas' => ['nullable', 'string', 'max:100'],
            'gugusBinaan' => ['nullable', 'string', 'max:255'],
            'program' => ['nullable', 'string', 'max:255'],
            'statusDosen' => ['nullable', 'string', 'max:100'],
        ]);

        return view('dashboard.lrc', ['lrc' => $dashboard->data($validated)]);
    }
}
