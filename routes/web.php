<?php

use App\Http\Controllers\DownloadController;
use App\Http\Controllers\TestingController;
use Illuminate\Support\Facades\Route;

Route::get("/", fn() => redirect("/admin"));

// Route::get('testing', [TestingController::class,'index'])->name('testing');
// Route::post('testing/upload', [TestingController::class, 'upload'])
//   ->name('testing.upload');

Route::get('download/{path}/{extension}', [DownloadController::class, 'index'])
  ->name('download')
  ->middleware('signed');