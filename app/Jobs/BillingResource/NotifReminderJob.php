<?php

namespace App\Jobs\BillingResource;

use App\Mail\BillingResource\NotifReminderMail;
use App\Models\Billing;
use App\Models\BillingStatus;
use App\Models\EmailLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

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
    $now = now()->toDateTimeString();

    $daysStr = getSetting('billing_due_reminder_days', '1 Hari');
    $days    = (int) str_replace(' Hari', '', $daysStr);

    $startDate = now()->toDateString();
    $endDate   = now()->addDays($days)->toDateString();

    $billing = Billing::whereBetween('due_date', [$startDate, $endDate])
      ->whereNotIn('billing_status_id', [BillingStatus::PAID])
      ->first();

    if (!$billing) return;

    $summary = Billing::selectRaw('
      SUM(CASE WHEN billing_period_id = 1 THEN amount ELSE 0 END) AS daily,
      SUM(CASE WHEN billing_period_id = 2 THEN amount ELSE 0 END) AS weekly,
      SUM(CASE WHEN billing_period_id = 3 THEN amount ELSE 0 END) AS monthly,
      COUNT(CASE WHEN billing_period_id = 1 THEN 1 ELSE NULL END) AS daily_count,
      COUNT(CASE WHEN billing_period_id = 2 THEN 1 ELSE NULL END) AS weekly_count,
      COUNT(CASE WHEN billing_period_id = 3 THEN 1 ELSE NULL END) AS monthly_count
    ')->whereBetween('due_date', [$startDate, $endDate])
      ->whereNotIn('billing_status_id', [BillingStatus::PAID])
      ->first();

    $data = [
      'log_name'      => 'billing_reminder_notification',
      'email'         => config('app.author_email'),
      'subject'       => 'Notifikasi: Pengingat Tagihan Pembayaran',
      'created_at'    => $now,
      'summary'       => $summary,
      'reminder_days' => $daysStr,
    ];

    $mailObj = new NotifReminderMail($data);
    $message = $mailObj->render();

    EmailLog::create([
      'status_id'  => 2,
      'name'       => $data['log_name'],
      'email'      => $data['email'],
      'subject'    => $data['subject'],
      'message'    => $message,
      'created_at' => $now,
      'updated_at' => $now,
    ]);

    Mail::to($data['email'])->queue(new NotifReminderMail($data));
  }
}
