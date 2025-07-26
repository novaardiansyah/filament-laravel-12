<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\ShortUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

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

    $shortUrl->increment('clicks');
    $shortUrl->save();
    
    $data = [
      'seconds' => 3,
      'short_url' => $shortUrl,
    ];

    // ! Simpan log klik
    $ip_info = Http::get("https://ipinfo.io/{$ip_address}/json?token=" . config(key: 'services.ipinfo.token'))->json();

    $country     = $ip_info['country'] ?? null;
    $city        = $ip_info['city'] ?? null;
    $region      = $ip_info['region'] ?? null;
    $postal      = $ip_info['postal'] ?? null;
    $geolocation = $ip_info['loc'] ?? [];
    $timezone    = $ip_info['timezone'] ?? null;
    $url         = $shortUrl->tiny_url ?? $shortUrl->short_url;

    ActivityLog::create([
      'log_name'     => 'Access',
      'description'  => "Someone clicked the short URL: {$url}",
      'event'        => 'Visit Short URL',
      'subject_type' => ShortUrl::class,
      'subject_id'   => $shortUrl->id,
      'properties' => [
        'clicks'    => $shortUrl->clicks,
        'short_url' => $shortUrl->short_url,
        'tiny_url'  => $shortUrl->tiny_url,
        'long_url'  => $shortUrl->long_url,
      ],
      'ip_address'  => $ip_address,
      'user_agent'  => $user_agent,
      'country'     => $country,
      'city'        => $city,
      'region'      => $region,
      'postal'      => $postal,
      'geolocation' => $geolocation,
      'timezone'    => $timezone,
      'referer'     => route('short-urls.show', ['shortUrl' => $shortUrl->code]),
    ]);

    return response()->json(['data' => $data], 200);
  }
}
