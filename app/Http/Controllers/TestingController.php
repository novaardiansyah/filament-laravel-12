<?php

namespace App\Http\Controllers;

use App\Mail\ContactMessageResource\NotifContactMail;
use App\Mail\PaymentResource\ScheduledPaymentMail;
use App\Models\Payment;
use App\Models\PaymentAccount;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class TestingController extends Controller
{
  public function __construct()
  {
    $env = config('app.env');
    if ($env != 'local') {
      abort(404, 'Not Found');
    }
  }

  public function index(Request $request)
  {
    $preview = (bool) $request->input('preview', 0);

    $data = [
      'email'   => 'novaardiansyah78@gmail.com',
      'subject' => 'Notifikasi: Pesan masuk baru dari situs web',
    ];

    if (!$preview) {
      Mail::to($data['email'])->queue(new NotifContactMail($data));
      echo 'Email has been queued for sending.';
    }

    $process = new NotifContactMail($data);
    return $process->render();
  }

  public function email_preview(Request $request)
  {
    $preview = (bool) $request->input('preview', 0);

    $now   = now()->toDateTimeString();
    $today = now()->toDateString();

    $send = [
      'filename'   => 'scheduled-payment-report',
      'title'      => 'Laporan keuangan terjadwal',
      'start_date' => $today,
      'end_date'   => $today,
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
      'log_name'         => 'scheduled_payment_notification',
      'email'            => config('app.author_email'),
      'subject'          => 'Notifikasi: Ringkasan Laporan Keuangan Terjadwal',
      'payment_accounts' => PaymentAccount::orderBy('deposit', 'desc')->get()->toArray(),
      'payment'          => $payment->toArray(),
      'date'             => carbonTranslatedFormat($now, 'd F Y'),
      'created_at'       => $now,
      'attachments' => [
        storage_path('app/' . $result['filepath']),
      ],
    ];

    if (!$preview) {
      Mail::to($data['email'])->queue(new ScheduledPaymentMail($data));
      echo 'Email has been queued for sending.';
    }

    $process = new ScheduledPaymentMail($data);
    return $process->render();
  }
}
