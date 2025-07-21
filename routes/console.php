<?php

use App\Jobs\PaymentAccountResource\DailyReportJob;
use App\Jobs\PaymentResource\ScheduledPaymentJob;
use App\Jobs\ScheduledFileDeletionResource\DailyFileCleanupJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
  $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');



if (config('app.env') != 'staging') {
  Schedule::job(new DailyReportJob())
    ->dailyAt('23:45');

  Schedule::job(new ScheduledPaymentJob())
    ->dailyAt('23:00');

  Schedule::job(new DailyFileCleanupJob())
    ->dailyAt('23:59');
}