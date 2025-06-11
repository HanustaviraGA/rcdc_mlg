<?php

use Illuminate\Support\Facades\Route;
use Modules\PerhitunganKPIPKM\Http\Controllers\PerhitunganKPIPKMController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('perhitungankpipkm', PerhitunganKPIPKMController::class)->names('perhitungankpipkm');
});
