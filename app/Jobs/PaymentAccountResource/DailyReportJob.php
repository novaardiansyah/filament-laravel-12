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

    $send = [
      'filename'   => 'daily-payment-report',
      'title'      => 'Laporan keuangan harian',
      'start_date' => $startDate,
      'end_date'   => $endDate,
      'now'        => $now,
    ];

    $result = PaymentService::make_pdf($send);

    $data = [
      'email'            => config('app.author_email'),
      'subject'          => 'Notifikasi: Ringkasan Laporan Keuangan Harian',
      'payment_accounts' => PaymentAccount::orderBy('deposit', 'desc')->get()->toArray(),
      'date'             => carbonTranslatedFormat($now, 'd F Y'),
      'log_name'         => 'daily_payment_notification',
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
