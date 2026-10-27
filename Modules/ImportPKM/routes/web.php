<?php

use Illuminate\Support\Facades\Route;
use Modules\ImportPKM\Http\Controllers\CommunityPartnerImportController;
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

Route::prefix('backoffice/importpkm')->group(function () {
    Route::post('partners/preview', [CommunityPartnerImportController::class, 'preview'])->middleware('auth')->name('importpkm.partners.preview');
    Route::post('partners/upload', [CommunityPartnerImportController::class, 'upload'])->middleware('auth')->name('importpkm.partners.upload');
    Route::get('/', [ImportPKMController::class, 'index'])->name('importpkm.index');
    Route::post('preview', [ImportPKMController::class, 'preview'])->middleware('auth')->name('importpkm.preview');
    Route::post('upload', [ImportPKMController::class, 'upload'])->middleware('auth')->name('importpkm.upload');
    Route::post('create', [ImportPKMController::class, 'create'])->name('importpkm.create');
    Route::post('read', [ImportPKMController::class, 'read'])->name('importpkm.read');
    Route::put('update', [ImportPKMController::class, 'update'])->name('importpkm.update');
    Route::delete('delete', [ImportPKMController::class, 'delete'])->name('importpkm.delete');
});
