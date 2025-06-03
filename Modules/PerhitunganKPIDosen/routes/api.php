<?php

use Illuminate\Support\Facades\Route;
use Modules\PerhitunganKPIDosen\Http\Controllers\PerhitunganKPIDosenController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('perhitungankpidosen', PerhitunganKPIDosenController::class)->names('perhitungankpidosen');
});
