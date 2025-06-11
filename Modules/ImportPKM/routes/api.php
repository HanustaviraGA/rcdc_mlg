<?php

use Illuminate\Support\Facades\Route;
use Modules\ImportPKM\Http\Controllers\ImportPKMController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('importpkm', ImportPKMController::class)->names('importpkm');
});
