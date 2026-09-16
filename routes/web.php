<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SpreadsheetController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LandingController;
use Illuminate\Support\Facades\Auth;
// use Session;

Route::get('/', [LandingController::class, 'home'])->name('home');
Route::get('/lecturers', [LandingController::class, 'lecturers'])->name('lecturers');
Route::get('/lecturers/detail/{kode_dosen}', [LandingController::class, 'lecture_detail'])->name('lecture_detail');
Route::get('/dashboard', [DashboardController::class, 'index'])->name('index');
Route::get('/kpi-publikasi', [\App\Http\Controllers\PublicationDashboardController::class, 'index'])->name('publication-dashboard');
Route::get('/dashboard/kpi-publikasi', function (Request $request) {
    return redirect()->route('publication-dashboard', $request->query(), 301);
});
Route::get('/dashboard/import-hibah', [\App\Http\Controllers\ResearchImportController::class, 'index'])->name('research-import.index');
Route::post('/research-imports', [\App\Http\Controllers\ResearchImportController::class, 'store'])->name('research-import.store');
Route::get('/research-gallery', [\App\Http\Controllers\ResearchGalleryController::class, 'index'])->name('research-gallery.index');
Route::get('/research-gallery/{source}/{id}', [\App\Http\Controllers\ResearchGalleryController::class, 'show'])
    ->whereIn('source', ['rectorate', 'system'])->where('id', '[a-f0-9]{64}')->name('research-gallery.show');
Route::get('/dashboard/{any}', [DashboardController::class, 'index_spec'])->name('index_spec');
Route::post('loadpage', function(Request $request){
    $destination = $request->destination; // Get the destination from the request
    return redirect(url('backoffice/'.$destination)); // Redirect to the module route
})->name('loadpage');
Route::get('/read', [SpreadsheetController::class, 'read'])->name('read');
Route::get('/read_xlsx', [SpreadsheetController::class, 'read_xlsx'])->name('read_xlsx');

// Change Permission
Route::get('/change_perms', [DashboardController::class, 'change_perms'])->name('change_perms');

// Sync
Route::get('/sync_research_ac_id', [LandingController::class, 'sync_research_ac_id'])->name('sync_research_ac_id');
Route::get('/sync_comdev_ac_id', [LandingController::class, 'sync_comdev_ac_id'])->name('sync_comdev_ac_id');
