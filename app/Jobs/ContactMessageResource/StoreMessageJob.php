<?php

namespace App\Jobs\ContactMessageResource;

use App\Mail\ContactMessageResource\NotifContactMail;
use App\Mail\ContactMessageResource\ReplyContactMail;
use App\Models\ActivityLog;
use App\Models\ContactMessage;
use App\Models\EmailLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Mail;

class StoreMessageJob implements ShouldQueue
{
  use Queueable;

  /**
   * Create a new job instance.
   */
  public function __construct(public array $data)
  {
    // You can initialize any properties or perform any setup here
  }

  /**
   * Execute the job.
   */
  public function handle(): void
  {
    $now = now()->toDateTimeString();

    $ip_address = explode(',', $this->data['ip_address'] ?? '')[0] ?: null;
    $ip_info    = Http::get("https://ipinfo.io/{$ip_address}/json?token=" . config(key: 'services.ipinfo.token'))->json();

    $country = $ip_info['country'] ?? null;
    $city    = $ip_info['city'] ?? null;
    $region  = $ip_info['region'] ?? null;
    $postal  = $ip_info['postal'] ?? null;
    
    $address = null;

    if ($city) {
      $address_parts = array_filter([$city, $region, $country]);
      $address       = implode(', ', $address_parts);
      if ($postal) $address .= " ({$postal})";
    }

    $geolocation = $ip_info['loc'] ?? null;
    $geolocation = $geolocation ? str_replace(',', ', ', $geolocation) : null;

    $timezone = $ip_info['timezone'] ?? null;

    $data = array_merge($this->data, [
      'ip_address' => $ip_address,
    ]);

    $contactMessage = ContactMessage::create($data);

    $notif_reply = [
      'log_name'   => 'reply_contact_message',
      'email'      => $data['email'],
      'subject'    => 'Terima Kasih Telah Menghubungi Saya',
      'name'       => $data['name'],
      'created_at' => $now,
    ];

    $mailObj = new ReplyContactMail($notif_reply);
    $message = $mailObj->render();

    EmailLog::create([
      'status_id'  => 2,
      'name'       => $notif_reply['log_name'],
      'email'      => $notif_reply['email'],
      'subject'    => $notif_reply['subject'],
      'message'    => $message,
      'created_at' => $now,
      'updated_at' => $now,
    ]);

    Mail::to($contactMessage->email)->queue(new ReplyContactMail($notif_reply));

    $notif_params = array_merge($data, [
      'subject'         => 'Notifikasi: Pesan masuk baru dari situs web',
      'email'           => config('app.author_email'),
      'log_name'        => 'notif_contact_message',
      'email_contact'   => $data['email'],
      'subject_contact' => $data['subject'],
      'address'         => $address,
      'timezone'        => $timezone,
      'geolocation'     => $geolocation,
      'created_at'      => $now,
    ]);

    $mailObj = new NotifContactMail($notif_params);
    $message = $mailObj->render();

    EmailLog::create([
      'status_id'  => 2,
      'name'       => $notif_params['log_name'],
      'email'      => $notif_params['email'],
      'subject'    => $notif_params['subject'],
      'message'    => $message,
      'created_at' => $now,
      'updated_at' => $now,
    ]);

    Mail::to($notif_params['email'])->queue(new NotifContactMail($notif_params));

    ActivityLog::create([
      'log_name'     => 'Resource',
      'description'  => "New contact message from {$contactMessage->name} ({$contactMessage->email})",
      'event'        => 'New Contact Message',
      'subject_id'   => $contactMessage->id,
      'subject_type' => ContactMessage::class,
      'ip_address'   => $ip_address,
      'country'      => $country,
      'city'         => $city,
      'region'       => $region,
      'postal'       => $postal,
      'geolocation'  => $geolocation,
      'timezone'     => $timezone,
      'referer'      => $contactMessage->url,
      'user_agent'   => $contactMessage->user_agent,
      'properties' => [
        'name'    => $contactMessage->name,
        'email'   => $contactMessage->email,
        'subject' => $contactMessage->subject,
      ],
    ]);
  }
}
