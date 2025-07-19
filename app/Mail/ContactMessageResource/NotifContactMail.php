<?php

namespace App\Mail\ContactMessageResource;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;

class NotifContactMail extends Mailable implements ShouldQueue
{
  use Queueable, SerializesModels;

  /**
   * Create a new message instance.
   */
  public function __construct(public array $data = [])
  {
    //
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
    // * view('mails.contact-message-resource.notify-contact-mail');
    return new Content(
      view: 'mails.contact-message-resource.notify-contact-mail',
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
}
