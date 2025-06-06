<?php

use Illuminate\Support\Facades\Route;
use Modules\PerhitunganKPISelfDev\Http\Controllers\PerhitunganKPISelfDevController;

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

Route::prefix('backoffice/perhitungankpiselfdev')->group(function() {
    Route::get('/', [PerhitunganKPISelfDevController::class, 'index'])->name('perhitungankpiselfdev.index');
    Route::post('create', [PerhitunganKPISelfDevController::class, 'create'])->name('perhitungankpiselfdev.create');
    Route::post('read', [PerhitunganKPISelfDevController::class, 'read'])->name('perhitungankpiselfdev.read');
    Route::put('update', [PerhitunganKPISelfDevController::class, 'update'])->name('perhitungankpiselfdev.update');
    Route::delete('delete', [PerhitunganKPISelfDevController::class, 'delete'])->name('perhitungankpiselfdev.delete');
});
