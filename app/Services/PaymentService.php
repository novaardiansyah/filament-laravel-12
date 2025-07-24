<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\User;

class PaymentService
{
  public static function make_pdf(array $data)
  {
    \Log::info('['. __METHOD__.':'.__LINE__ .']: Generate PDF report process started');

    $notification = $data['notification'] ?? false;
    $auto_close_tbody = $data['auto_close_tbody'] ?? false;
    
    $startDate = $data['start_date'] ?? now()->startOfMonth();
    $endDate   = $data['end_date'] ?? now()->endOfMonth();
    $now       = $data['now'] ?? now()->toDateTimeString();

    $start_date = carbonTranslatedFormat($startDate, 'd');
    $end_date   = carbonTranslatedFormat($endDate, 'd F Y');

    if (carbonTranslatedFormat($startDate, 'F Y') != carbonTranslatedFormat($endDate, 'F Y')) {
      $start_date = carbonTranslatedFormat($startDate, 'd F Y');
      $end_date   = carbonTranslatedFormat($endDate, 'd F Y');
    }

    $periode = "{$start_date} - {$end_date}";

    // ! Setup pdf attachment
    $mpdf          = new \Mpdf\Mpdf();
    $rowIndex      = 1;
    $totalExpense  = 0;
    $totalIncome   = 0;
    $totalTransfer = 0;
    $user          = auth()->user() ?? User::find(1);  // ! Default user if not authenticated

    $mpdf->WriteHTML(view('payment-resource.make-pdf.header', [
      'title'   => $data['title'] ?? 'Laporan keuangan',
      'now'     => carbonTranslatedFormat($now, 'd/m/Y H:i'),
      'periode' => $periode,
      'user'    => $user,
    ])->render());
    
    Payment::whereBetween('date', [$startDate, $endDate])
      ->orderBy('date', 'desc')
      ->chunk(200, function ($list) use ($mpdf, &$rowIndex, &$totalExpense, &$totalIncome, &$totalTransfer) {
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
          } else {
            $totalTransfer += $record->amount;
          }
        }
    });

    $mpdf->WriteHTML('
      </tbody>
      <tfoot>
        <tr>
          <td colspan="4" style="text-align: center; font-weight: bold;">Total Transaksi</td>
          <td style="font-weight: bold;">'. ($totalTransfer > 0 ? toIndonesianCurrency($totalTransfer) : '') .'</td>
          <td style="font-weight: bold;">'. ($totalIncome > 0 ? toIndonesianCurrency($totalIncome) : '') .'</td>
          <td style="font-weight: bold;">'. ($totalExpense > 0 ? toIndonesianCurrency($totalExpense) : '') .'</td>
        </tr>
      </tfoot>
    ');

    $result = makePdf($mpdf, $data['filename'] ?? 'payment-report', $user, notification: $notification, auto_close_tbody: $auto_close_tbody);

    \Log::info('['. __METHOD__.':'.__LINE__ .']: Generate PDF report executed successfully');

    return $result;
  }
}
