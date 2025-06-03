<?php

use Illuminate\Support\Facades\Route;
use Modules\PerhitunganKPIDosen\Http\Controllers\PerhitunganKPIDosenController;

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

Route::prefix('backoffice/perhitungankpidosen')->group(function() {
    Route::get('/', [PerhitunganKPIDosenController::class, 'index'])->name('perhitungankpidosen.index');
    Route::post('create', [PerhitunganKPIDosenController::class, 'create'])->name('perhitungankpidosen.create');
    Route::post('read', [PerhitunganKPIDosenController::class, 'read'])->name('perhitungankpidosen.read');
    Route::put('update', [PerhitunganKPIDosenController::class, 'update'])->name('perhitungankpidosen.update');
    Route::delete('delete', [PerhitunganKPIDosenController::class, 'delete'])->name('perhitungankpidosen.delete');
    // Custom
    Route::post('init_table', [PerhitunganKPIDosenController::class, 'init_table'])->name('perhitungankpidosen.init_table');
});
