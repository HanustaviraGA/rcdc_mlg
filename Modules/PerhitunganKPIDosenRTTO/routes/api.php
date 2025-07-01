<?php

use Illuminate\Support\Facades\Route;
use Modules\PerhitunganKPIDosenRTTO\Http\Controllers\PerhitunganKPIDosenRTTOController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('perhitungankpidosenrtto', PerhitunganKPIDosenRTTOController::class)->names('perhitungankpidosenrtto');
});
