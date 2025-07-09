<?php

namespace App\Jobs\PaymentAccountResource;

use App\Mail\PaymentAccountResource\DailyReportMail;
use App\Models\PaymentAccount;
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
    $now = now()->format('Y-m-d H:i:s');

    $data = [
      'email'            => config('app.author_email'),
      'subject'          => 'Ringkasan Laporan Harian Akun Keuangan',
      'payment_accounts' => PaymentAccount::orderBy('deposit', 'desc')->get()->toArray(),
      'date'             => now()->translatedFormat('d F Y'),
      'created_at'       => $now,
    ];

    Mail::to($data['email'])->queue(new DailyReportMail($data));
  }
}
