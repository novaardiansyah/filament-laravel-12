<?php

namespace App\Jobs\PaymentAccountResource;

use App\Mail\PaymentAccountResource\DailyReportMail;
use App\Models\EmailLog;
use App\Models\Payment;
use App\Models\PaymentAccount;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class DailyReportJob implements ShouldQueue
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
    \Log::info('['. __METHOD__.':'.__LINE__ .']: Daily Report Job process started');

    $startDate = now()->startOfWeek();
    $endDate   = now()->endOfWeek();
    $now       = now()->toDateTimeString();
    $today     = now()->toDateString();

    $send = [
      'filename'   => 'daily-payment-report',
      'title'      => 'Laporan keuangan harian',
      'start_date' => $startDate,
      'end_date'   => $endDate,
      'now'        => $now,
    ];

    $result = PaymentService::make_pdf($send);

    $payment = Payment::selectRaw('
      SUM(CASE WHEN type_id = 1 AND date = ? THEN expense ELSE 0 END) AS daily_expense,
      SUM(CASE WHEN type_id = 2 AND date = ? THEN income ELSE 0 END) AS daily_income,
      SUM(CASE WHEN type_id != 1 AND type_id != 2 AND date = ? THEN amount ELSE 0 END) AS daily_other,
      COUNT(CASE WHEN type_id = 1 AND date = ? THEN id END) AS daily_expense_count,
      COUNT(CASE WHEN type_id = 2 AND date = ? THEN id END) AS daily_income_count,
      COUNT(CASE WHEN type_id != 1 AND type_id != 2 AND date = ? THEN id END) AS daily_other_count
    ', [
      $today, $today, $today, 
      $today, $today, $today
    ])->first();

    $data = [
      'log_name'         => 'daily_payment_notification',
      'email'            => config('app.author_email'),
      'subject'          => 'Notifikasi: Ringkasan Laporan Keuangan Harian',
      'payment_accounts' => PaymentAccount::orderBy('deposit', 'desc')->get()->toArray(),
      'payment'          => $payment->toArray(),
      'date'             => carbonTranslatedFormat($now, 'd F Y'),
      'created_at'       => $now,
      'attachments' => [
        storage_path('app/' . $result['filepath']),
      ],
    ];

    $mailObj = new DailyReportMail($data);
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

    Mail::to($data['email'])->queue(new DailyReportMail($data));

    \Log::info('['. __METHOD__.':'.__LINE__ .']: Daily Report Job executed successfully');
  }
}
