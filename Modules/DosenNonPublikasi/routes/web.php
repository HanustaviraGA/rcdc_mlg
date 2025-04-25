<?php

use Illuminate\Support\Facades\Route;
use Modules\DosenNonPublikasi\Http\Controllers\DosenNonPublikasiController;

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

Route::prefix('backoffice/dosennonpublikasi')->group(function() {
    Route::get('/', [DosenNonPublikasiController::class, 'index'])->name('dosennonpublikasi.index');
    Route::post('create', [DosenNonPublikasiController::class, 'create'])->name('dosennonpublikasi.create');
    Route::post('read', [DosenNonPublikasiController::class, 'read'])->name('dosennonpublikasi.read');
    Route::put('update', [DosenNonPublikasiController::class, 'update'])->name('dosennonpublikasi.update');
    Route::delete('delete', [DosenNonPublikasiController::class, 'delete'])->name('dosennonpublikasi.delete');
    // Custom
    Route::post('init_table', [DosenNonPublikasiController::class, 'init_table'])->name('dosennonpublikasi.init_table');
    Route::post('init_chart', [DosenNonPublikasiController::class, 'init_chart'])->name('dosennonpublikasi.init_chart');
});
