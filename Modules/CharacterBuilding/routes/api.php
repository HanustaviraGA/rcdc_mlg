<?php

use Illuminate\Support\Facades\Route;
use Modules\CharacterBuilding\Http\Controllers\CharacterBuildingController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('characterbuilding', CharacterBuildingController::class)->names('characterbuilding');
});
