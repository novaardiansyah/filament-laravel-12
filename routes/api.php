<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContactMessageController;
use App\Http\Controllers\PaymentAccountController;
use App\Http\Controllers\ShortUrlController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'auth'], function () {
  Route::post('login', [AuthController::class, 'login']);
  // Route::post('register', [AuthController::class, 'register']);
  // Route::post('verify-email', [AuthController::class, 'verifyOTP']);
  // Route::post('resend-verify-email', [AuthController::class, 'resendOTP']);

  // Route::group(['middleware' => 'auth:sanctum'], function () {
  //   // ! From external application (sanctum required)
  //   Route::post('send-otp-register', [AuthController::class, 'sendRegisterOtp']);

  //   Route::post('logout', [AuthController::class, 'logout']);
  //   Route::get('me', [AuthController::class, 'me']);
  // });
});

// payment account resource
Route::apiResource('payment-accounts', PaymentAccountController::class)
    ->only(['index', 'show', 'store', 'update', 'destroy'])
    ->middleware(['auth:sanctum']);

Route::apiResource('contact-messages', ContactMessageController::class)
    ->only(['store'])
    ->middleware(['auth:sanctum']);

Route::get('short-urls/{shortUrl:code}', [ShortUrlController::class, 'show'])
    ->name('short-urls.show')
    ->middleware(['auth:sanctum']);
