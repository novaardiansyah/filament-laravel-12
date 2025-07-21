<?php

namespace App\Http\Controllers;

use App\Mail\ContactMessageResource\NotifContactMail;
use App\Mail\PaymentAccountResource\WeeklyReportMail;
use App\Models\Payment;
use App\Models\PaymentAccount;
use App\Models\User;
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

    $startDate = now()->startOfWeek();
    $endDate   = now()->endOfWeek();
    $now       = now()->toDateTimeString();

    $start_date = carbonTranslatedFormat($startDate, 'd');
    $end_date   = carbonTranslatedFormat($endDate, 'd F Y');

    if (carbonTranslatedFormat($startDate, 'F Y') != carbonTranslatedFormat($endDate, 'F Y')) {
      $start_date = carbonTranslatedFormat($startDate, 'd F Y');
      $end_date   = carbonTranslatedFormat($endDate, 'd F Y');
    }

    $periode = "{$start_date} - {$end_date}";

    // ! Setup pdf attachment
    $mpdf         = new \Mpdf\Mpdf();
    $rowIndex     = 1;
    $totalExpense = 0;
    $totalIncome  = 0;
    $user         = auth()->user() ?? User::find(1);  // ! Default user if not authenticated

    $mpdf->WriteHTML(view('payment-resource.make-pdf.header', [
      'title'   => 'Laporan keuangan mingguan',
      'now'     => carbonTranslatedFormat($now, 'd/m/Y H:i'),
      'periode' => $periode,
      'user'    => $user,
    ])->render());
    
    Payment::whereBetween('date', [$startDate, $endDate])
      ->chunk(200, function ($list) use ($mpdf, &$rowIndex, &$totalExpense, &$totalIncome) {
        foreach ($list as $record) {
          $view = view('payment-resource.make-pdf.body', [
            'record'    => $record,
            'loopIndex' => $rowIndex++,
          ])->render();

          $mpdf->WriteHTML($view);

          if ($record->type_id == 1) {
            $totalExpense += $record->amount;
          } elseif ($record->type_id == 2) {
            $totalIncome += $record->amount;
          }
        }
    });

    $mpdf->WriteHTML('
      <tr>
        <td colspan="4" style="text-align: center; font-weight: bold;">Total Transaksi</td>
        <td style="font-weight: bold;">'. ($totalIncome > 0 ? toIndonesianCurrency($totalIncome) : '') .'</td>
        <td style="font-weight: bold;">'. ($totalExpense > 0 ? toIndonesianCurrency($totalExpense) : '') .'</td>
      </tr>
    ');

    $result = makePdf($mpdf, 'weekly-payment-report', $user, notification: false);

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
      'attachments' => [
        storage_path('app/' . $result['filepath']),
      ],
    ];

    if (!$preview) {
      Mail::to($data['email'])->queue(new WeeklyReportMail($data));
      echo 'Email has been queued for sending.';
    }

    $process = new WeeklyReportMail($data);
    return $process->render();
  }
}
