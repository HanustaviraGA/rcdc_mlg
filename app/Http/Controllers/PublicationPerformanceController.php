<?php

namespace App\Http\Controllers;

use App\Services\CommunityDevelopment\CommunityDevelopmentDashboard;
use App\Services\Publications\PublicationDashboard;
use App\Services\Publications\PublicationPerformanceDashboard;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PublicationPerformanceController extends Controller
{
    public function index(Request $request, PublicationPerformanceDashboard $dashboard, CommunityDevelopmentDashboard $communityDevelopment): View
    {
        $validated = $request->validate(['community_period' => ['nullable', 'integer', 'min:1']]);

        return view('dashboard.publication_kpi', [
            'performance' => $dashboard->data(),
            'communityDevelopment' => $communityDevelopment->data(isset($validated['community_period']) ? (int) $validated['community_period'] : null),
        ]);
    }

    public function legacy(PublicationDashboard $dashboard): View
    {
        return view('dashboard.publication_kpi_old', ['dashboard' => $dashboard->data()]);
    }
}
