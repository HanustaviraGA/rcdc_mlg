<?php

use Illuminate\Support\Facades\Route;
use Modules\HakAkses\Http\Controllers\HakAksesController;

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

Route::prefix('backoffice/hakakses')->group(function() {
    Route::get('/', [HakAksesController::class, 'index'])->name('hakakses.index');
    Route::post('create', [HakAksesController::class, 'create'])->name('hakakses.create');
    Route::post('read', [HakAksesController::class, 'read'])->name('hakakses.read');
    Route::put('update', [HakAksesController::class, 'update'])->name('hakakses.update');
    Route::delete('delete', [HakAksesController::class, 'delete'])->name('hakakses.delete');
    // Custom
    Route::post('getMenuList', [HakAksesController::class, 'getMenuList'])->name('hakakses.getMenuList');
    Route::post('getRoleList', [HakAksesController::class, 'getRoleList'])->name('hakakses.getRoleList');
});
