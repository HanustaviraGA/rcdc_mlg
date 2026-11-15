<?php

use Illuminate\Support\Facades\Route;
use Modules\RoadmapLRC\Http\Controllers\RoadmapLRCController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('roadmaplrc', RoadmapLRCController::class)->names('roadmaplrc');
});
