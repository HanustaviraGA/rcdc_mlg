<?php

use Illuminate\Support\Facades\Route;
use Modules\PerhitunganKPI\Http\Controllers\PerhitunganKPIController;

Route::prefix('backoffice/perhitungankpi')->group(function () {
    Route::get('/', [PerhitunganKPIController::class, 'index'])->name('perhitungankpi.index');
    Route::post('init_table', [PerhitunganKPIController::class, 'init_table'])->name('perhitungankpi.init_table');
});
