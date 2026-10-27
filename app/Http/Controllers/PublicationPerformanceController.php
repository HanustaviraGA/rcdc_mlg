<?php

namespace App\Http\Controllers;

use App\Services\Publications\PublicationDashboard;
use App\Services\Publications\PublicationPerformanceDashboard;
use Illuminate\Contracts\View\View;

class PublicationPerformanceController extends Controller
{
    public function index(PublicationPerformanceDashboard $dashboard): View
    {
        return view('dashboard.publication_kpi', [
            'performance' => $dashboard->data(),
        ]);
    }

    public function legacy(PublicationDashboard $dashboard): View
    {
        return view('dashboard.publication_kpi_old', ['dashboard' => $dashboard->data()]);
    }
}
