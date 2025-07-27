<?php

namespace App\Jobs\ContactMessageResource;

use App\Mail\ContactMessageResource\NotifContactMail;
use App\Mail\ContactMessageResource\ReplyContactMail;
use App\Models\ContactMessage;
use App\Models\EmailLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
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
    $contactMessage = ContactMessage::create($this->data);

    $notif_reply = [
      'log_name'   => 'reply_contact_message',
      'email'      => $contactMessage->email,
      'subject'    => 'Terima Kasih Telah Menghubungi Saya',
      'name'       => $contactMessage->name,
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

    $notif_params = array_merge($contactMessage->toArray(), [
      'log_name'        => 'notif_contact_message',
      'email_contact'   => $contactMessage->email,
      'email'           => config('app.author_email'),
      'subject_contact' => $contactMessage->subject,
      'subject'         => 'Notifikasi: Pesan masuk baru dari situs web',
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
  }
}
