<?php

use Illuminate\Support\Facades\Route;
use Modules\PerhitunganKPIPKM\Http\Controllers\PerhitunganKPIPKMController;

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

Route::prefix('backoffice/perhitungankpipkm')->group(function() {
    Route::get('/', [PerhitunganKPIPKMController::class, 'index'])->name('perhitungankpipkm.index');
    Route::post('create', [PerhitunganKPIPKMController::class, 'create'])->name('perhitungankpipkm.create');
    Route::post('read', [PerhitunganKPIPKMController::class, 'read'])->name('perhitungankpipkm.read');
    Route::put('update', [PerhitunganKPIPKMController::class, 'update'])->name('perhitungankpipkm.update');
    Route::delete('delete', [PerhitunganKPIPKMController::class, 'delete'])->name('perhitungankpipkm.delete');
    // Custom
    Route::post('init_table', [PerhitunganKPIPKMController::class, 'init_table'])->name('perhitungankpipkm.init_table');
});
