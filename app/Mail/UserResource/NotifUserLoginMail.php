<?php

namespace App\Mail\UserResource;

use App\Models\EmailLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;

class NotifUserLoginMail extends Mailable implements ShouldQueue
{
  use Queueable, SerializesModels;

  /**
   * Create a new message instance.
   */
  public function __construct(public array $data)
  {
    $this->updateToSuccess();
  }

  /**
   * Get the message envelope.
   */
  public function envelope(): Envelope
  {
    return new Envelope(
      subject: $this->data['subject'],
      replyTo: [
        new Address(config('app.author_email'), config('app.author_name')),
      ]
    );
  }

  /**
   * Get the message content definition.
   */
  public function content(): Content
  {
    // * view('mails.user-resource.notif-user-login-mail');
    return new Content(
      view: 'mails.user-resource.notif-user-login-mail',
    );
  }

  /**
   * Get the attachments for the message.
   *
   * @return array<int, \Illuminate\Mail\Mailables\Attachment>
   */
  public function attachments(): array
  {
    return [];
  }

  public function updateToSuccess()
  {
    $createdAt = $this->data['created_at'];
    $logName   = $this->data['log_name'];

    $response = 'User login notification has been successfully sent.';

    $update = EmailLog::where('created_at', $createdAt)
      ->where('name', $logName)
      ->update([
        'status_id' => 3,
        'response'  => $response
      ]);
      
    if ($update) {
      \Log::info($response);
    }
  }

  public function failed(\Exception $exception)
  {
    $createdAt = $this->data['created_at'];
    $logName   = $this->data['log_name'];
    $email     = $this->data['email'];

    $response = 'Failed to send User login notification to ' . $email;

    $update = EmailLog::where('created_at', $createdAt)
      ->where('name', $logName)
      ->update([
        'status_id' => 4,
        'response'  => substr($response . ': {' . $exception->getMessage() . '}', 0, 3000)
      ]);

    if ($update) {
      \Log::info($response);
    }
  }
}
