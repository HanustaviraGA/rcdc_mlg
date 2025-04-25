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

Route::prefix('backoffice/exportreport')->group(function() {
    Route::get('/', [ExportReportController::class, 'index'])->name('exportreport.index');
    Route::post('create', [ExportReportController::class, 'create'])->name('exportreport.create');
    Route::post('read', [ExportReportController::class, 'read'])->name('exportreport.read');
    Route::put('update', [ExportReportController::class, 'update'])->name('exportreport.update');
    Route::delete('delete', [ExportReportController::class, 'delete'])->name('exportreport.delete');
    // Custom
    Route::post('generate_pdf', [ExportReportController::class, 'generate_pdf'])->name('exportreport.generate_pdf');
});
