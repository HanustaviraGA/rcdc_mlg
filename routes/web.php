<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SpreadsheetController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Auth;
// use Session;

Route::get('/', function () {
    // Session::flush();
    Auth::logout();
    // return view('welcome');
    return redirect()->to('/dashboard/');
})->name('landing');
Route::get('/dashboard', [DashboardController::class, 'index'])->name('index');
Route::get('/dashboard/{any}', [DashboardController::class, 'index_spec'])->name('index_spec');
Route::post('loadpage', function(Request $request){
    $destination = $request->destination; // Get the destination from the request
    return redirect(url('backoffice/'.$destination)); // Redirect to the module route
})->name('loadpage');
Route::get('/read', [SpreadsheetController::class, 'read'])->name('read');
Route::get('/read_xlsx', [SpreadsheetController::class, 'read_xlsx'])->name('read_xlsx');

// Change Permission
Route::get('/change_perms', [DashboardController::class, 'change_perms'])->name('change_perms');
