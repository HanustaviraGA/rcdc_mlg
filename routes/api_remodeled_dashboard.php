<?php

use App\Http\Controllers\PublicationDashboardRemodelController;
use Illuminate\Support\Facades\Route;

/**
 * API routes untuk dashboard KPI publikasi yang dirombak (remodeled)
 * 
 * Routes di sini adalah public (tanpa auth middleware)
 * Admin-only routes ditandai dengan middleware 'auth'
 */

// Public API routes
Route::get('/api/publication-dashboard/summary', [PublicationDashboardRemodelController::class, 'getSummary']);
Route::get('/api/publication-dashboard/department', [PublicationDashboardRemodelController::class, 'getDepartmentDetail']);
Route::get('/api/publication-dashboard/publications', [PublicationDashboardRemodelController::class, 'getFacultyPublications']);

// Admin-only API routes
Route::middleware(['auth'])->group(function () {
    Route::post('/api/publication-dashboard/summary', [PublicationDashboardRemodelController::class, 'saveSummary']);
    Route::post('/api/publication-dashboard/card', [PublicationDashboardRemodelController::class, 'saveCard']);
    Route::delete('/api/publication-dashboard/card/{id}', [PublicationDashboardRemodelController::class, 'deleteCard']);
});
