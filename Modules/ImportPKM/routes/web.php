<?php

use Illuminate\Support\Facades\Route;
use Modules\ImportPKM\Http\Controllers\ImportPKMController;

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

Route::prefix('backoffice/importpkm')->group(function() {
    Route::get('/', [ImportPKMController::class, 'index'])->name('importpkm.index');
    Route::post('create', [ImportPKMController::class, 'create'])->name('importpkm.create');
    Route::post('read', [ImportPKMController::class, 'read'])->name('importpkm.read');
    Route::put('update', [ImportPKMController::class, 'update'])->name('importpkm.update');
    Route::delete('delete', [ImportPKMController::class, 'delete'])->name('importpkm.delete');
});
