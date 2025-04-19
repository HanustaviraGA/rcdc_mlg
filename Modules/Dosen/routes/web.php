<?php

use Illuminate\Support\Facades\Route;
use Modules\Dosen\Http\Controllers\DosenController;

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

Route::prefix('backoffice/dosen')->group(function() {
    Route::get('/', [DosenController::class, 'index'])->name('dosen.index');
    Route::post('create', [DosenController::class, 'create'])->name('dosen.create');
    Route::post('read', [DosenController::class, 'read'])->name('dosen.read');
    Route::put('update', [DosenController::class, 'update'])->name('dosen.update');
    Route::delete('delete', [DosenController::class, 'delete'])->name('dosen.delete');
});
