<?php

namespace App\Http\Controllers;

use App\Services\CommunityDevelopment\CommunityDevelopmentDashboard;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class CommunityDevelopmentController extends Controller
{
    public function index(Request $request, CommunityDevelopmentDashboard $dashboard): View
    {
        $validated = $request->validate([
            'community_period' => ['nullable', 'integer', 'min:1'],
            'partner_year' => ['nullable', 'integer', 'between:2000,2100'],
        ]);

        return view('dashboard.comdev', ['communityDevelopment' => $dashboard->data(
            isset($validated['community_period']) ? (int) $validated['community_period'] : null,
            isset($validated['partner_year']) ? (int) $validated['partner_year'] : null,
        )]);
    }
}
