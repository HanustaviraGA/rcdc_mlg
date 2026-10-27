<?php

use Illuminate\Support\Facades\Route;
use Modules\ImportRectorate\Http\Controllers\ColumnMappingController;
use Modules\ImportRectorate\Http\Controllers\ImportRectorateController;
use Modules\ImportRectorate\Http\Middleware\VerifyImportDeletionCsrfToken;

Route::prefix('backoffice/importrectorate')->group(function () {
    Route::get('/', [ImportRectorateController::class, 'index'])->name('importrectorate.index');
    Route::post('upload', [ImportRectorateController::class, 'store'])->name('importrectorate.upload');
    Route::delete('imports/{type}/{year}/{month}', [ImportRectorateController::class, 'destroyPeriod'])
        ->middleware(VerifyImportDeletionCsrfToken::class)->name('importrectorate.destroy');

    // Column Mapping Preview
    Route::post('preview-columns', [ColumnMappingController::class, 'preview'])->name('importrectorate.preview-columns');
});
