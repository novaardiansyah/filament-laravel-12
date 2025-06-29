<?php

namespace App\Providers;

use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Listeners\AssignDemoRoleToUser;

class EventServiceProvider extends ServiceProvider
{
  /**
   * The event to listener mappings for the application.
   *
   * @var array<class-string, array<int, class-string>>
   */
  protected $listen = [
    Verified::class => [
      AssignDemoRoleToUser::class,
    ],
  ];

  /**
   * Register any events for your application.
   */
  public function boot(): void
  {
    //
  }
}
