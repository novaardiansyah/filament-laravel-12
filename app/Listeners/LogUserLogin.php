<?php

namespace App\Listeners;

use App\Mail\UserResource\NotifUserLoginMail;
use App\Models\ActivityLog;
use App\Models\EmailLog;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\TelegramLocationNotification;
use App\Notifications\TelegramNotification;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

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

    // ! Cek di ActivityLog dengan IP, jika sudah ada, tidak perlu kirim email lagi
    $interval = Setting::where('key', 'interval_login_notification')->first()?->value ?? '24 Jam';
    $interval = (int) preg_replace('/\D/', '', $interval);

    $existingLog = ActivityLog::where('ip_address', $ip_address)
      ->where('event', 'Login Notification')
      ->where('created_at', '>=', now()->subHours($interval))
      ->first();

    if ($existingLog) return;

    // ! Ambil IP pertama jika ada beberapa
    $ip_address = explode(',', $ip_address)[0];
    $ip_info    = Http::get("https://ipinfo.io/{$ip_address}/json?token=" . config(key: 'services.ipinfo.token'))->json();

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

    $timezone = $ip_info['timezone'] ?? null;
    $now      = now();

    $log = [
      'log_name'    => 'Notification',
      'description' => 'Email Login Notification Sent',
      'event'       => 'Login Notification',
      'causer_type' => User::class,
      'causer_id'   => $event->user->id,
      'ip_address'  => $ip_address,
      'country'     => $country,
      'city'        => $city,
      'region'      => $region,
      'postal'      => $postal,
      'geolocation' => $geolocation,
      'timezone'    => $timezone,
      'user_agent'  => request()->userAgent(),
      'referer'     => url('admin/login'),
      'properties'  => [
        'email_user' => $event->user->email,
      ],
    ];

    ActivityLog::create($log);

    $data = [
      'email'       => config('app.author_email'),
      'log_name'    => 'notif_user_login',
      'subject'     => 'Notifikasi: Login pengguna dari situs web',
      'email_user'  => $event->user->email,
      'ip_address'  => $log['ip_address'],
      'geolocation' => $log['geolocation'],
      'user_agent'  => $log['user_agent'],
      'timezone'    => $log['timezone'],
      'referer'     => $log['referer'],
      'address'     => $address,
      'created_at'  => $now,
    ];

    // ! Kirim notifikasi ke Email
    $emailEnabled = getSetting('email_login_notification', 'Tidak');
    if (textLower($emailEnabled) === 'ya') {
      \Log::info('784 --> Send login notification to email');

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

    // ! Kirim notifikasi ke Telegram
    $telegramEnabled = getSetting('telegram_login_notification', 'Tidak');
    if (textLower($telegramEnabled) === 'ya') {
      \Log::info('785 --> Send login notification to Telegram');

      $telegramMsg  = view('user-resource.telegram.notif-user-login', $data)->render();
      $telegramLoc = null;
  
      if ($geolocation) {
        $gelocationData = explode(',', $geolocation);
  
        $telegramLoc = [
          'latitude'  => $gelocationData[0] ?? null,
          'longitude' => $gelocationData[1] ?? null,
        ];
      }
  
      sendTelegramNotification($telegramMsg, $telegramLoc);
    }
  }
}
