<?php

namespace App\Jobs\ScheduledFileDeletionResource;

use App\Models\ActivityLog;
use App\Models\ScheduledFileDeletion;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DailyFileCleanupJob implements ShouldQueue
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
    $now  = now();
    $user = auth()->user() ?? User::find(1);  // ! Default user if not authenticated
    
    $subject = ScheduledFileDeletion::class;
    $causer_type = User::class;

    $files = ScheduledFileDeletion::where([
      ['has_been_deleted', '=', false],
      ['scheduled_deletion_time', '<=', $now],
    ]);

    $files->each(function (ScheduledFileDeletion $file)use ($user, $subject, $causer_type, &$logs) {
      $deletion = $file->deleteFile();

     ActivityLog::create([
        'log_name'     => 'Schedule',
        'description'  => "{$user->name} has deleted scheduled file: {$file->file_name}",
        'event'        => 'File Deletion',
        'causer_type'  => $causer_type,
        'causer_id'    => $user->id,
        'subject_type' => $subject,
        'subject_id'   => $file->id,
        'properties'   => array_merge($deletion, [
          'file_name'    => $file->file_name,
          'file_path'    => $file->file_path,
          'download_url' => $file->download_url,
        ]),
      ]);
    });
  }
}
