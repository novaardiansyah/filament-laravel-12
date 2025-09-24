<?php

use App\Jobs\BillingResource\NotifReminderJob;
use App\Jobs\NoteResource\NotifReminderJob AS NoteNotifReminderJob;
use App\Jobs\PaymentAccountResource\DailyReportJob;
use App\Jobs\PaymentAccountResource\WeeklyReportJob;
use App\Jobs\PaymentResource\ScheduledPaymentJob;
use App\Jobs\ScheduledFileDeletionResource\DailyFileCleanupJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
  $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

if (config('app.env') != 'staging') {
  // ! Notifikasi: Pengingat Tagihan Pembayaran
  Schedule::job(new NotifReminderJob())
    ->dailyAt('00:05');

  // ! Notifikasi: Laporan Keuangan Harian
  // Schedule::job(new DailyReportJob())
  //   ->dailyAt('23:45');

  // ! Notifikasi: Ringkasan Laporan Keuangan Mingguan
  // Schedule::job(new WeeklyReportJob())
  //   ->weeklyOn(7, '23:45');

  // ! Proses: Pembayaran Terjadwal
  // Schedule::job(new ScheduledPaymentJob())
  //   ->dailyAt('00:05');

  // ! Proses: Pembersihan File Harian
  Schedule::job(new DailyFileCleanupJob())
    ->dailyAt('23:59');

  // ! Proses: Pengingat Notifikasi Catatan
  Schedule::job(new NoteNotifReminderJob())
    ->everyTwoHours();
}