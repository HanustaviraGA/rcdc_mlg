<?php

namespace App\Http\Controllers;

use App\Services\CharacterBuilding\CharacterBuildingDashboard;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class CharacterBuildingDashboardController extends Controller
{
    public function index(Request $request, CharacterBuildingDashboard $dashboard): View
    {
        $validated = $request->validate([
            'year' => ['nullable', 'integer', 'between:2000,2100'],
            'quarter' => ['nullable', 'integer', 'between:1,4'],
        ]);

        return view('dashboard.cbdc', ['cbdc' => $dashboard->data(
            isset($validated['year']) ? (int) $validated['year'] : null,
            isset($validated['quarter']) ? (int) $validated['quarter'] : null,
        )]);
    }
}
