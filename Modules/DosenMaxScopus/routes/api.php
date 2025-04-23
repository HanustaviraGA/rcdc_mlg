<?php

use Illuminate\Support\Facades\Route;
use Modules\DosenMaxScopus\Http\Controllers\DosenMaxScopusController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('dosenmaxscopus', DosenMaxScopusController::class)->names('dosenmaxscopus');
});
