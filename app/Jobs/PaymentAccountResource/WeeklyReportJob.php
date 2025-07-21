<?php

namespace App\Jobs\PaymentAccountResource;

use App\Mail\PaymentAccountResource\WeeklyReportMail;
use App\Models\EmailLog;
use App\Models\Payment;
use App\Models\PaymentAccount;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class WeeklyReportJob implements ShouldQueue
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
    $startDate = now()->startOfWeek();
    $endDate   = now()->endOfWeek();

    $payment = Payment::selectRaw('
      SUM(CASE WHEN type_id = 1 THEN amount ELSE 0 END) as total_expense,
      SUM(CASE WHEN type_id = 2 THEN amount ELSE 0 END) as total_income,
      AVG(CASE WHEN type_id = 1 THEN amount ELSE NULL END) as avg_expense,
      AVG(CASE WHEN type_id = 2 THEN amount ELSE NULL END) as avg_income,
      COUNT(CASE WHEN type_id = 1 THEN 1 ELSE NULL END) as count_expense,
      COUNT(CASE WHEN type_id = 2 THEN 1 ELSE NULL END) as count_income
    ')
    ->whereBetween('date', [$startDate, $endDate])
    ->first();

    $sisa_saldo = PaymentAccount::sum('deposit');

    $start_date = carbonTranslatedFormat($startDate, 'd');
    $end_date = carbonTranslatedFormat($endDate, 'd F Y');

    if (carbonTranslatedFormat($startDate, 'F Y') != carbonTranslatedFormat($endDate, 'F Y')) {
      $start_date = carbonTranslatedFormat($startDate, 'd F Y');
      $end_date = carbonTranslatedFormat($endDate, 'd F Y');
    }

    $periode = "{$start_date} - {$end_date}";
    $now = now()->toDateTimeString();

    $data = [
      'log_name'      => 'weekly_payment_notification',
      'email'         => config('app.author_email'),
      'subject'       => 'Notifikasi: Ringkasan Laporan Keuangan Mingguan',
      'total_expense' => (int) $payment->total_expense ?? 0,
      'total_income'  => (int) $payment->total_income ?? 0,
      'avg_expense'   => (int) $payment->avg_expense ?? 0,
      'avg_income'    => (int) $payment->avg_income ?? 0,
      'count_expense' => (int) $payment->count_expense ?? 0,
      'count_income'  => (int) $payment->count_income ?? 0,
      'sisa_saldo'    => (int) $sisa_saldo,
      'periode'       => $periode,
      'created_at'    => $now,
    ];

    $mailObj = new WeeklyReportMail($data);
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

    Mail::to($data['email'])->queue(new WeeklyReportMail($data));
  }
}
