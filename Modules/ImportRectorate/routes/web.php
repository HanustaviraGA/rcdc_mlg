<?php

use Illuminate\Support\Facades\Route;
use Modules\ImportRectorate\Http\Controllers\ImportRectorateController;
use Modules\ImportRectorate\Http\Controllers\ColumnMappingController;

Route::prefix('backoffice/importrectorate')->group(function () {
    Route::get('/', [ImportRectorateController::class, 'index'])->name('importrectorate.index');
    Route::post('upload', [ImportRectorateController::class, 'store'])->name('importrectorate.upload');

    // Column Mapping Preview
    Route::post('preview-columns', [ColumnMappingController::class, 'preview'])->name('importrectorate.preview-columns');
    Route::post('validate-mapping', [ColumnMappingController::class, 'validateMapping'])->name('importrectorate.validate-mapping');
    Route::get('mapping-config', [ColumnMappingController::class, 'getConfig'])->name('importrectorate.mapping-config');
});
