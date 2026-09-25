<?php

use Illuminate\Support\Facades\Route;
use Modules\ImportRectorate\Http\Controllers\ImportRectorateController;

Route::prefix('backoffice/importrectorate')->group(function () {
    Route::get('/', [ImportRectorateController::class, 'index'])->name('importrectorate.index');
    Route::post('upload', [ImportRectorateController::class, 'store'])->name('importrectorate.upload');
});
