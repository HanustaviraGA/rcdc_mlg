<?php

namespace App\Http\Controllers;

use App\Services\Publications\PublicationDashboard;
use Illuminate\Contracts\View\View;

class PublicationDashboardController extends Controller
{
    public function index(PublicationDashboard $dashboard): View
    {
        return view('dashboard.publication_kpi', ['dashboard' => $dashboard->data()]);
    }
}
