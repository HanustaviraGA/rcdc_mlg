<?php

use Illuminate\Support\Facades\Route;
use Modules\MatrixKPIDosen\Http\Controllers\MatrixKPIDosenController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('matrixkpidosen', MatrixKPIDosenController::class)->names('matrixkpidosen');
});
