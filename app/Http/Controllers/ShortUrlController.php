<?php

namespace App\Http\Controllers;

use App\Models\ShortUrl;
use Illuminate\Http\Request;

class ShortUrlController extends Controller
{
  public function index(Request $request, ShortUrl $shortUrl)
  {
    if (!$shortUrl->is_active) {
      return abort(404, 'Short URL tidak ditemukan atau sudah tidak aktif.');
    }

    $shortUrl->increment('clicks');
    $shortUrl->save();

    $data = [
      'countdown' => 1,
      'data'      => $shortUrl,
    ];

    return view('short-url-resource.redirect', $data);
  }
}
