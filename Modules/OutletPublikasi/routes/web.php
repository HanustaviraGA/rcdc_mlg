<?php

use Illuminate\Support\Facades\Route;
use Modules\OutletPublikasi\Http\Controllers\OutletPublikasiController;

Route::get('outlet-publikasi', [OutletPublikasiController::class, 'publicIndex'])
    ->name('outletpublikasi.public');

Route::prefix('backoffice/outletpublikasi')->group(function () {
    Route::get('/', [OutletPublikasiController::class, 'index'])->name('outletpublikasi.index');
    Route::post('init_table', [OutletPublikasiController::class, 'init_table'])->name('outletpublikasi.init_table');
    Route::post('create', [OutletPublikasiController::class, 'create'])->name('outletpublikasi.create');
    Route::post('read', [OutletPublikasiController::class, 'read'])->name('outletpublikasi.read');
    Route::put('update', [OutletPublikasiController::class, 'update'])->name('outletpublikasi.update');
    Route::delete('delete', [OutletPublikasiController::class, 'delete'])->name('outletpublikasi.delete');
});
