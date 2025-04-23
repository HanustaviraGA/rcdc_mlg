<?php

use Illuminate\Support\Facades\Route;
use Modules\ImportRectorate\Http\Controllers\ImportRectorateController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('importrectorate', ImportRectorateController::class)->names('importrectorate');
});
