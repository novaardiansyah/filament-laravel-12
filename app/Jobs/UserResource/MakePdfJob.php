<?php

namespace App\Jobs\UserResource;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Database\Eloquent\Model;

class MakePdfJob implements ShouldQueue
{
  use Queueable;

  /**
   * Create a new job instance.
   */
  public function __construct(
    public Model $user
  ) { }

  /**
   * Execute the job.
   */
  public function handle(): bool
  {
    $now = now()->translatedFormat('d/m/Y H:i');
    
    $rowIndex = 1;
    $rowsView = '';

    User::with(['roles:id,name'])->chunk(200, function ($users) use (&$rowIndex, &$rowsView) {
      foreach ($users as $user) {
        $rowsView .= view('UserResource.MakePdfRow', [
          'item'      => $user,
          'loopIndex' => $rowIndex++,
        ])->render();
      }
    });

    $view = view('UserResource.MakePdf', [
      'rows' => $rowsView,
      'now'  => $now,
    ]);

    $result = makePdf('users', $this->user, $view);

    return $result;
  }
}
