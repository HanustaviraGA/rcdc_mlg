<?php

use Illuminate\Support\Facades\Route;
use Modules\RCDCEvent\Http\Controllers\RCDCEventController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('rcdcevent', RCDCEventController::class)->names('rcdcevent');
});
