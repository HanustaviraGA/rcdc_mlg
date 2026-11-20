<?php

use Illuminate\Support\Facades\Route;
use Modules\RoadmapLRC\Http\Controllers\RoadmapLRCController;

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

Route::prefix('backoffice/roadmaplrc')->group(function () {
    Route::get('/', [RoadmapLRCController::class, 'index'])->name('roadmaplrc.index');
    Route::post('upload/preview', [RoadmapLRCController::class, 'previewUpload'])->name('roadmaplrc.upload.preview');
    Route::post('upload/import', [RoadmapLRCController::class, 'importUpload'])->name('roadmaplrc.upload.import');
    Route::post('create', [RoadmapLRCController::class, 'create'])->name('roadmaplrc.create');
    Route::post('read', [RoadmapLRCController::class, 'read'])->name('roadmaplrc.read');
    Route::put('update', [RoadmapLRCController::class, 'update'])->name('roadmaplrc.update');
    Route::delete('delete', [RoadmapLRCController::class, 'delete'])->name('roadmaplrc.delete');
});
