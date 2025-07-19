<?php

namespace App\Jobs\PaymentAccountResource;

use App\Mail\PaymentAccountResource\DailyReportMail;
use App\Models\EmailLog;
use App\Models\Payment;
use App\Models\PaymentAccount;
use App\Models\User;
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
    $now = now();
    
    $mpdf     = new \Mpdf\Mpdf();
    $rowIndex = 1;
    $periode  = $now->toDateString();
    $user     = auth()->user() ?? User::find(1); // ! Default user if not authenticated

    $mpdf->WriteHTML(view('payment-resource.make-pdf.header', [
      'title'   => 'Laporan keuangan harian',
      'now'     => carbonTranslatedFormat($now, 'd/m/Y H:i'),
      'periode' => carbonTranslatedFormat($periode, 'l, d F Y'),
      'user'    => $user,
    ])->render());
    
    Payment::where([
      'date' => $periode,
    ])->chunk(200, function ($list) use ($mpdf, &$rowIndex) {
      foreach ($list as $record) {
        $view = view('payment-resource.make-pdf.body', [
          'record'    => $record,
          'loopIndex' => $rowIndex++,
        ])->render();

        $mpdf->WriteHTML($view);
      }
    });

    $result = makePdf($mpdf, 'daily-payment-report', $user);

    $data = [
      'email'            => config('app.author_email'),
      'subject'          => 'Notifikasi: Ringkasan Laporan Keuangan Harian',
      'payment_accounts' => PaymentAccount::orderBy('deposit', 'desc')->get()->toArray(),
      'date'             => carbonTranslatedFormat($periode, 'd F Y'),
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
  }
}
