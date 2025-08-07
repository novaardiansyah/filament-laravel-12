<?php

use App\Http\Controllers\DownloadController;
use App\Http\Controllers\ShortUrlController;
use App\Http\Controllers\TestingController;
use Illuminate\Support\Facades\Route;

Route::get("/", fn() => redirect("/admin"));

// Route::get('testing', [TestingController::class,'index'])
//   ->middleware('auth')
//   ->name('testing');

Route::group(['middleware' => 'auth', 'prefix' => 'testing', 'as' => 'testing.'], function () {
  Route::get('/', [TestingController::class, 'index'])
    ->name('index');

  Route::get('/email-preview', [TestingController::class, 'email_preview'])
    ->name('email_preview');

  Route::get('/pdf-preview', [TestingController::class, 'pdf_preview'])
    ->name('pdf_preview');

  Route::get('/telegram-bot', [TestingController::class, 'telegram_bot'])
    ->name('telegram_bot');
});

Route::get('download/{path}/{extension}', [DownloadController::class, 'index'])
  ->name('download')
  ->middleware('signed');
  