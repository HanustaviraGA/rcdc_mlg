<?php

use Illuminate\Support\Facades\Route;
use Modules\HakAkses\Http\Controllers\HakAksesController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('hakakses', HakAksesController::class)->names('hakakses');
});
