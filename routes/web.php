<?php

use App\Http\Controllers\DownloadController;
use Illuminate\Support\Facades\Route;

Route::get("/", fn() => redirect("/admin"));

Route::get('download/{path}/{extension}', [DownloadController::class, 'index'])->name('download');