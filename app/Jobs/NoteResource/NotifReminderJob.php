<?php

namespace App\Jobs\NoteResource;

use App\Models\Note;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class NotifReminderJob implements ShouldQueue
{
  use Queueable;

  /**
   * Create a new job instance.
   */
  public function __construct()
  {
    //
  }

  /**
   * Execute the job.
   */
  public function handle(): void
  {
    $jobTitle = 'Notification Note Reminder Job';

    \Log::info("987 --> {$jobTitle} process started");

    $now = now();
    $twoHours = Carbon::now()->addHours(2)->toDateTimeString();

    Note::where('send_notification',  true)
      ->whereBetween('notification_at', [$now, $twoHours])
      ->chunk(10, function ($records) {
        foreach ($records as $record) {
          $data = [
            'code'            => $record->code,
            'title'           => $record->title,
            'notification_at' => $record->notification_at,
            'view_link'       => url('admin/notes?tableAction=view&tableActionRecord=' . $record->id)
          ];

          $telegramMsg = view('note-resource.telegram.notif-reminder', $data)->render();
          sendTelegramNotification($telegramMsg);

          $record->update([
            'send_notification' => false,
          ]);
        }
      });

    \Log::info("988 --> {$jobTitle} executed successfully");
  }
}
