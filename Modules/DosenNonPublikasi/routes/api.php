<?php

use Illuminate\Support\Facades\Route;
use Modules\DosenNonPublikasi\Http\Controllers\DosenNonPublikasiController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('dosennonpublikasi', DosenNonPublikasiController::class)->names('dosennonpublikasi');
});
