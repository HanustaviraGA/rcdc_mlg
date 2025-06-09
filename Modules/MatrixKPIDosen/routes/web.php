<?php

use Illuminate\Support\Facades\Route;
use Modules\MatrixKPIDosen\Http\Controllers\MatrixKPIDosenController;

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

Route::prefix('backoffice/matrixkpidosen')->group(function() {
    Route::get('/', [MatrixKPIDosenController::class, 'index'])->name('matrixkpidosen.index');
    Route::post('create', [MatrixKPIDosenController::class, 'create'])->name('matrixkpidosen.create');
    Route::post('read', [MatrixKPIDosenController::class, 'read'])->name('matrixkpidosen.read');
    Route::put('update', [MatrixKPIDosenController::class, 'update'])->name('matrixkpidosen.update');
    Route::delete('delete', [MatrixKPIDosenController::class, 'delete'])->name('matrixkpidosen.delete');
});
