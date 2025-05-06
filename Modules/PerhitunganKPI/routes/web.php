<?php

use Illuminate\Support\Facades\Route;
use Modules\PerhitunganKPI\Http\Controllers\PerhitunganKPIController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::prefix('backoffice/perhitungankpi')->group(function() {
    Route::get('/', [PerhitunganKPIController::class, 'index'])->name('perhitungankpi.index');
    Route::post('create', [PerhitunganKPIController::class, 'create'])->name('perhitungankpi.create');
    Route::post('read', [PerhitunganKPIController::class, 'read'])->name('perhitungankpi.read');
    Route::put('update', [PerhitunganKPIController::class, 'update'])->name('perhitungankpi.update');
    Route::delete('delete', [PerhitunganKPIController::class, 'delete'])->name('perhitungankpi.delete');
    // Custom
    Route::post('init_table', [PerhitunganKPIController::class, 'init_table'])->name('perhitungankpi.init_table');
    Route::post('cek_kpi', [PerhitunganKPIController::class, 'cek_kpi'])->name('perhitungankpi.cek_kpi');
});
