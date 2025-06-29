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
  public function handle(): void
  {
    $now  = now()->translatedFormat('d/m/Y H:i');
    $data = User::with(['roles:id,name'])->get();
    $view = view('UserResource.MakePdf', compact('data', 'now'));

    makePdf('users', $this->user, $view);
  }
}
