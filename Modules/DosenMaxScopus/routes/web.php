<?php

use Illuminate\Support\Facades\Route;
use Modules\DosenMaxScopus\Http\Controllers\DosenMaxScopusController;

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

Route::prefix('backoffice/dosenmaxscopus')->group(function() {
    Route::get('/', [DosenMaxScopusController::class, 'index'])->name('dosenmaxscopus.index');
    Route::post('create', [DosenMaxScopusController::class, 'create'])->name('dosenmaxscopus.create');
    Route::post('read', [DosenMaxScopusController::class, 'read'])->name('dosenmaxscopus.read');
    Route::put('update', [DosenMaxScopusController::class, 'update'])->name('dosenmaxscopus.update');
    Route::delete('delete', [DosenMaxScopusController::class, 'delete'])->name('dosenmaxscopus.delete');
    // Custom
    Route::post('init_table', [DosenMaxScopusController::class, 'init_table'])->name('dosenmaxscopus.init_table');
});
