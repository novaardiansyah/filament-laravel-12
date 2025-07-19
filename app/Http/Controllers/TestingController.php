<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TestingController extends Controller
{
  public function index()
  {
    $now = now()->translatedFormat('d/m/Y H:i');
    
    $mpdf     = new \Mpdf\Mpdf();
    $rowIndex = 1;
    $periode  = now()->subDays(2)->toDateString();
    $user     = auth()->user() ?? User::find(1); // ! Default user if not authenticated

    $mpdf->WriteHTML(view('payment-resource.make-pdf.header', [
      'title'   => 'Laporan keuangan harian',
      'now'     => $now,
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

    $result = makePdf($mpdf, 'daily-payment-report', $user, true);

    return $result;
  }
}
