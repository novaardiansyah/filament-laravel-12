<?php

use App\Jobs\PaymentResource\ScheduledPaymentJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
  $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new ScheduledPaymentJob())
  ->dailyAt('00:05')
  ->onFailure(fn () => \Log::error('ScheduledPaymentJob failed'))
  ->onSuccess(fn () => \Log::info('ScheduledPaymentJob completed successfully'));