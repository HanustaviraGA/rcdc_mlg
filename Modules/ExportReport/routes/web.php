<?php

use Illuminate\Support\Facades\Route;
use Modules\ExportReport\Http\Controllers\ExportReportController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::prefix('backoffice/exportreport')->group(function () {
    Route::get('/', [ExportReportController::class, 'index'])->name('exportreport.index');
    Route::post('generate', [ExportReportController::class, 'generate'])->name('exportreport.generate');
    Route::post('save', [ExportReportController::class, 'save'])->name('exportreport.save');
    Route::post('download', [ExportReportController::class, 'download'])->name('exportreport.download');
});
