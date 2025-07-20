<?php

namespace App\Listeners;

use App\Mail\UserResource\NotifUserLoginMail;
use App\Models\EmailLog;
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
    $ip_info    = Http::get("https://ipinfo.io/{$ip_address}/json?token=" . config(key: 'services.ipinfo.token'))->json();

    $country = $ip_info['country'] ?? '-';
    $city    = $ip_info['city'] ?? '-';
    $region  = $ip_info['region'] ?? '-';
    $postal  = $ip_info['postal'] ?? '-';

    $address = "{$city}, {$region}, {$country}, {$postal}";
    $address = trim(str_replace('-,', '', $address));

    $geolocation = $ip_info['loc'] ?? '-';
    $timezone    = $ip_info['timezone'] ?? '-';
    $now         = now();

    $data = [
      'log_name'    => 'notif_user_login',
      'email'       => config('app.author_email'),
      'subject'     => 'Notifikasi: Login pengguna dari situs web',
      'email_user'  => $event->user->email,
      'ip_address'  => $ip_address,
      'user_agent'  => request()->userAgent(),
      'address'     => $address,
      'geolocation' => $geolocation,
      'timezone'    => $timezone,
      'url'         => url('admin/login'),
      'date'        => $now,
      'created_at'  => $now,
    ];

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
