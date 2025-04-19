<?php

use Illuminate\Support\Facades\Route;
use Modules\RCDCEvent\Http\Controllers\RCDCEventController;

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

Route::prefix('backoffice/rcdcevent')->group(function() {
    Route::get('/', [RCDCEventController::class, 'index'])->name('rcdcevent.index');
    Route::post('create', [RCDCEventController::class, 'create'])->name('rcdcevent.create');
    Route::post('read', [RCDCEventController::class, 'read'])->name('rcdcevent.read');
    Route::put('update', [RCDCEventController::class, 'update'])->name('rcdcevent.update');
    Route::delete('delete', [RCDCEventController::class, 'delete'])->name('rcdcevent.delete');
});
