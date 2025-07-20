<?php

namespace App\Http\Controllers;

use App\Mail\ContactMessageResource\NotifContactMail;
use App\Mail\UserResource\NotifUserLoginMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class TestingController extends Controller
{
  public function __construct()
  {
    $env = config('app.env');
    if ($env != 'local') {
      abort(404, 'Not Found');
    }
  }

  public function index(Request $request)
  {
    $preview = (bool) $request->input('preview', 0);

    $data = [
      'email'   => 'novaardiansyah78@gmail.com',
      'subject' => 'Notifikasi: Pesan masuk baru dari situs web',
    ];

    if (!$preview) {
      Mail::to($data['email'])->queue(new NotifContactMail($data));
      echo 'Email has been queued for sending.';
    }

    $process = new NotifContactMail($data);
    return $process->render();
  }

  public function email_preview(Request $request)
  {
    $preview = (bool) $request->input('preview', 0);

    $ip_address = request()->ip();
    $ip_info    = Http::get("https://ipinfo.io/{$ip_address}/json?token=" . config(key: 'services.ipinfo.token'))->json();

    $country = $ip_info['country'] ?? '-';
    $city    = $ip_info['city'] ?? '-';
    $region  = $ip_info['region'] ?? '-';
    $postal  = $ip_info['postal'] ?? '-';

    $address = "{$city}, {$region}, {$country}, {$postal}";
    $address = trim(str_replace('-,', '', $address));

    $geolocation = $ip_info['loc'] ?? '-';
    $timezone    = $ip_info['timezone'] ?? '-';

    $data = [
      'email'       => config('app.author_email'),
      'subject'     => 'Notifikasi: Login pengguna dari situs web',
      'email_user'  => auth()->user()->email,
      'ip_address'  => $ip_address,
      'user_agent'  => request()->userAgent(),
      'address'     => $address,
      'geolocation' => $geolocation,
      'timezone'    => $timezone,
      'url'         => url('admin/login'),
      'date'        => now()->toDateTimeString(),
    ];

    if (!$preview) {
      Mail::to($data['email'])->queue(new NotifUserLoginMail($data));
      echo 'Email has been queued for sending.';
    }

    $process = new NotifUserLoginMail($data);
    return $process->render();
  }
}
