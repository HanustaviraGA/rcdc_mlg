<?php

use Illuminate\Support\Facades\Route;
use Modules\ImportRectorate\Http\Controllers\ImportRectorateController;

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

Route::prefix('backoffice/importrectorate')->group(function() {
    Route::get('/', [ImportRectorateController::class, 'index'])->name('importrectorate.index');
    Route::post('create', [ImportRectorateController::class, 'create'])->name('importrectorate.create');
    Route::post('read', [ImportRectorateController::class, 'read'])->name('importrectorate.read');
    Route::put('update', [ImportRectorateController::class, 'update'])->name('importrectorate.update');
    Route::delete('delete', [ImportRectorateController::class, 'delete'])->name('importrectorate.delete');
});
