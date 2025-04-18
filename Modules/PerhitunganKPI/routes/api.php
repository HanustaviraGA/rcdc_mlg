<?php

use Illuminate\Support\Facades\Route;
use Modules\PerhitunganKPI\Http\Controllers\PerhitunganKPIController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('perhitungankpi', PerhitunganKPIController::class)->names('perhitungankpi');
});
