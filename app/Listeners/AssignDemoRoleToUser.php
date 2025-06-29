<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class AssignDemoRoleToUser implements ShouldQueue
{
  /**
   * Handle the event.
   */
  public function handle(object $event): void
  {
    \Log::info(__METHOD__ . ':' . __LINE__, ['assigning demo role to user' => $event->user->id]);
    $event->user->assignRole('demo');
  }
}
