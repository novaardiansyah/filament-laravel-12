<?php

namespace App\Models;

use App\Mail\EmailResource\SendMail;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Mail;

class Email extends Model
{
  use SoftDeletes;
  
  protected $table = 'emails';
  protected $guarded = ['id'];
  protected $casts = [
    'attachments' => 'array',
    'has_send'    => 'boolean',
  ];

  public function email_log(): BelongsTo
  {
    return $this->belongsTo(EmailLog::class);
  }

  public function sendEmail(): void
  {
    $record = $this;

    $now = Carbon::now();

    $attachments = $record->attachments ?? [];
    foreach ($attachments as $key => $attachment) {
      $attachments[$key] = storage_path('app/public/' . $attachment);
    }

    $send = [
      'log_name'    => 'send_app_mail',
      'email'       => $record->recipient,
      'subject'     => $record->subject,
      'body'        => $record->body,
      'attachments' => $attachments,
      'created_at'  => $now,
    ];

    $mailObj = new SendMail($send);
    $message = $mailObj->render();

    $log = EmailLog::create([
      'status_id'  => 2,
      'name'       => $send['log_name'],
      'email'      => $send['email'],
      'subject'    => $send['subject'],
      'message'    => $message,
      'created_at' => $now,
      'updated_at' => $now,
    ]);

    Mail::to($send['email'])->queue(new SendMail($send));

    $record->update([
      'email_log_id' => $log->id,
      'has_send'     => true,
    ]);
  }
}
