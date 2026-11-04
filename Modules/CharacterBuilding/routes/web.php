<?php

use Illuminate\Support\Facades\Route;
use Modules\CharacterBuilding\Http\Controllers\CharacterBuildingController;

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

Route::prefix('backoffice/characterbuilding')->group(function () {
    Route::get('/', [CharacterBuildingController::class, 'index'])->name('characterbuilding.index');
    // Target scores: create replaces the target of the same period; update and delete take an id.
    Route::post('create', [CharacterBuildingController::class, 'create'])->name('characterbuilding.create');
    // Progress of the Publikasi or Comdev tab for a quarter.
    Route::post('read', [CharacterBuildingController::class, 'read'])->name('characterbuilding.read');
    Route::put('update', [CharacterBuildingController::class, 'update'])->name('characterbuilding.update');
    Route::delete('delete', [CharacterBuildingController::class, 'delete'])->name('characterbuilding.delete');
});
