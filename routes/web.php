<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SpreadsheetController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/read', [SpreadsheetController::class, 'read'])->name('read');

