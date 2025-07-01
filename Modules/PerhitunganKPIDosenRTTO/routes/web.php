<?php

use Illuminate\Support\Facades\Route;
use Modules\PerhitunganKPIDosenRTTO\Http\Controllers\PerhitunganKPIDosenRTTOController;

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

Route::prefix('backoffice/perhitungankpidosenrtto')->group(function() {
    Route::get('/', [PerhitunganKPIDosenRTTOController::class, 'index'])->name('perhitungankpidosenrtto.index');
    Route::post('create', [PerhitunganKPIDosenRTTOController::class, 'create'])->name('perhitungankpidosenrtto.create');
    Route::post('read', [PerhitunganKPIDosenRTTOController::class, 'read'])->name('perhitungankpidosenrtto.read');
    Route::put('update', [PerhitunganKPIDosenRTTOController::class, 'update'])->name('perhitungankpidosenrtto.update');
    Route::delete('delete', [PerhitunganKPIDosenRTTOController::class, 'delete'])->name('perhitungankpidosenrtto.delete');
});
