<?php

namespace App\Http\Controllers;

use App\Models\ShortUrl;
use Illuminate\Http\Request;

class ShortUrlController extends Controller
{
  public function show(Request $request, ShortUrl $shortUrl)
  {
    if (!$shortUrl->is_active) {
      return response()->json([
        'message' => 'Short URL tidak ditemukan atau sudah tidak aktif.'
      ], 404);
    }

    $ip_address = $request->ip();
    $user_agent = $request->header('User-Agent');

    \Log::info('['. __METHOD__.':'.__LINE__ .']: Somebody click our short URL', [
      'ip_address' => $ip_address,
      'user_agent' => $user_agent,
    ]);

    $shortUrl->increment('clicks');
    $shortUrl->save();

    $data = [
      'seconds' => 3,
      'short_url' => $shortUrl,
    ];

    return response()->json(['data' => $data], 200);
  }
}
