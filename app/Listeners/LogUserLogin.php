<?php

namespace App\Listeners;

use App\Mail\UserResource\NotifUserLoginMail;
use App\Models\EmailLog;
use App\Models\UserLog;
use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class LogUserLogin
{
  /**
   * Create the event listener.
   */
  public function __construct()
  {
    //
  }

  /**
   * Handle the event.
   */
  public function handle(Login $event): void
  {
    static $alreadyLogged = false;

    if ($alreadyLogged) {
      return;
    }

    $alreadyLogged = true;
    
    $ip_address = request()->ip();

    // ! Cek di UserLog dengan IP, jika dalam 30 menit sudah ada, tidak perlu kirim email lagi
    $existingLog = UserLog::where('ip_address', $ip_address)
      ->where('created_at', '>=', now()->subMinutes(30))
      ->first();
    
    if ($existingLog) {
      return;
    }

    $ip_info = Http::get("https://ipinfo.io/{$ip_address}/json?token=" . config(key: 'services.ipinfo.token'))->json();

    $country = $ip_info['country'] ?? null;
    $city    = $ip_info['city'] ?? null;
    $region  = $ip_info['region'] ?? null;
    $postal  = $ip_info['postal'] ?? null;
    
    $address = null;
    if ($city) {
      $address = trim("{$city}, {$region}, {$country} ({$postal})");
    }

    $geolocation = $ip_info['loc'] ?? null;
    $geolocation = $geolocation ? str_replace(',', ', ', $geolocation) : null;
    
    $timezone    = $ip_info['timezone'] ?? null;
    $now         = now();

    $saveLog = UserLog::create([
      'user_id'    => $event->user->id,
      'email'      => $event->user->email,
      'ip_address' => $ip_address,
      'country'    => $country,
      'city'       => $city,
      'region'     => $region,
      'postal'     => $postal,
      'geolocation'=> $geolocation,
      'timezone'   => $timezone,
      'user_agent' => request()->headers->get('user-agent'),
      'referer'    => request()->headers->get('referer'),
      'created_at' => $now,
      'updated_at' => $now,
    ]);

    $data = array_merge(
      $saveLog->toArray(),
        [
        'log_name'   => 'notif_user_login',
        'email_user' => $saveLog->email,
        'email'      => config('app.author_email'),
        'subject'    => 'Notifikasi: Login pengguna dari situs web',
        'address'    => $address,
        'created_at' => $now,
      ]
    );

    $mailObj = new NotifUserLoginMail($data);
    $message = $mailObj->render();

    EmailLog::create([
      'status_id'  => 2,
      'name'       => $data['log_name'],
      'email'      => $data['email'],
      'subject'    => $data['subject'],
      'message'    => $message,
      'created_at' => $now,
      'updated_at' => $now,
    ]);

    Mail::to($data['email'])->queue(new NotifUserLoginMail($data));
  }
}
