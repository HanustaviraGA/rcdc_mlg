<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SpreadsheetController;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return view('welcome');
})->name('landing');
Route::get('/dashboard', [DashboardController::class, 'index'])->name('index');
Route::get('/dashboard/{any}', [DashboardController::class, 'index_spec'])->name('index_spec');
Route::post('loadpage', function(Request $request){
    $destination = $request->destination; // Get the destination from the request
    return redirect(url('backoffice/'.$destination)); // Redirect to the module route
})->name('loadpage');
Route::get('/read', [SpreadsheetController::class, 'read'])->name('read');

