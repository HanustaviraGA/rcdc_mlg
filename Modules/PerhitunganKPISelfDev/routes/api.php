<?php

use Illuminate\Support\Facades\Route;
use Modules\PerhitunganKPISelfDev\Http\Controllers\PerhitunganKPISelfDevController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('perhitungankpiselfdev', PerhitunganKPISelfDevController::class)->names('perhitungankpiselfdev');
});
