<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramLocation;

class TelegramLocationNotification extends Notification implements ShouldQueue
{
  use Queueable;

  /**
   * Create a new notification instance.
   */
  public function __construct(private array $data = [])
  {
    //
  }

  /**
   * Get the notification's delivery channels.
   *
   * @return array<int, string>
   */
  public function via(object $notifiable): array
  {
    return ['telegram'];
  }

  /**
   * Get the mail representation of the notification.
   */
  public function toTelegram(object $notifiable): TelegramLocation
  {
    return TelegramLocation::create()
      ->latitude($this->data['latitude'] ?? '-6.245201142095669')
      ->longitude($this->data['longitude'] ?? '106.64952076742718');
  }

  /**
   * Get the array representation of the notification.
   *
   * @return array<string, mixed>
   */
  public function toArray(object $notifiable): array
  {
    return [
      //
    ];
  }
}
