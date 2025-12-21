<?php

use Illuminate\Support\Facades\Route;
use Modules\Home\Http\Controllers\HomeController;

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

Route::prefix('backoffice/home')->group(function() {
    Route::get('/', [HomeController::class, 'index'])->name('home.index');
    Route::post('create', [HomeController::class, 'create'])->name('home.create');
    Route::post('read', [HomeController::class, 'read'])->name('home.read');
    Route::put('update', [HomeController::class, 'update'])->name('home.update');
    Route::delete('delete', [HomeController::class, 'delete'])->name('home.delete');
    // Custom
    Route::post('init_table', [HomeController::class, 'init_table'])->name('home.init_table');
});
