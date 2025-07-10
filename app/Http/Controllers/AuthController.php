<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
  public function login(LoginRequest $request)
  {
    $validated = $request->validated();

    if (!Auth::attempt($validated, $request->remember_me)) {
      return response()->json(['message' => 'Email atau password Anda salah!'], 401);
    }

    if (!$request->user()->hasVerifiedEmail()) {
      return response()->json(['message' => 'Email Anda belum diverifikasi!'], 401);
    }

    $user = User::where('email', $validated['email'])->first();
    $token = $user->createToken('api_token');

    $expired_at = now()->addDays(14);
    $token->accessToken->expires_at = $expired_at;
    $token->accessToken->save();

    return response()->json([
      'access_token' => $token->plainTextToken,
      'token_type' => 'Bearer',
      'expires_at' => $expired_at,
    ]);
  }
}
