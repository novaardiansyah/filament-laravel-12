<?php

namespace App\Http\Controllers;

use App\Mail\BillingResource\NotifReminderMail;
use App\Mail\ContactMessageResource\NotifContactMail;
use App\Models\Billing;
use App\Models\BillingStatus;
use App\Models\EmailLog;
use App\Models\User;
use App\Notifications\TelegramLocationNotification;
use App\Notifications\TelegramNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

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

    $now = now()->toDateTimeString();

    $daysStr = getSetting('billing_due_reminder_days', '1 Hari');
    $days    = (int) str_replace(' Hari', '', $daysStr);

    $startDate = now()->toDateString();
    $endDate   = now()->addDays($days)->toDateString();

    $billing = Billing::whereBetween('billing_date', [$startDate, $endDate])
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
    ')->whereBetween('billing_date', [$startDate, $endDate])
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

    if (!$preview) {
      Mail::to($data['email'])->queue(new NotifReminderMail($data));
      echo 'Email has been queued for sending.';
    }

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

    $process = new NotifReminderMail($data);
    return $process->render();
  }

  public function pdf_preview(Request $request) 
  {
    $preview = (bool) $request->input('preview', 0);

    $user = auth()->user() ?? User::find(1);
    $now  = now()->toDateTimeString();

    $daysStr = getSetting('billing_due_reminder_days', '1 Hari');
    $days    = (int) str_replace(' Hari', '', $daysStr);

    $startDate = now()->toDateString();
    $endDate   = now()->addDays($days)->toDateString();
    
    $mpdf     = new \Mpdf\Mpdf();
    $rowIndex = 1;

    $mpdf->WriteHTML(view('billing-resource.make-pdf.header', [
      'now' => carbonTranslatedFormat($now, 'd/m/Y H:i'),
    ])->render());
    
    $total = 0;

    Billing::with(['billingPeriod:id,name', 'item:id,name', 'billingStatus:id,name', 'paymentAccount:id,name', 'payment:id,name'])
      ->whereBetween('billing_date', [$startDate, $endDate])
      ->whereNotIn('billing_status_id', [BillingStatus::PAID])  
      ->orderBy('billing_date', 'asc')
      ->chunk(200, function ($billings) use ($mpdf, &$rowIndex, &$total) {
        foreach ($billings as $data) {
          $total += $data->amount;

          $view = view('billing-resource.make-pdf.body', [
            'data'      => $data,
            'loopIndex' => $rowIndex++,
          ])->render();

          $mpdf->WriteHTML($view);
        }
      });
    
    $mpdf->WriteHTML('
      </tbody>
      <tfoot>
        <tr>
          <td colspan="6" style="text-align: center; font-weight: bold;">Total Transaksi</td>
          <td style="font-weight: bold; text-align: right;">'. ($total > 0 ? toIndonesianCurrency($total) : '') .'</td>
        </tr>
      </tfoot>
    ');

    $pdf = makePdf($mpdf, 'billing-reminder', $user, $preview, false, false);

    return response()->json($pdf);
  }

  public function telegram_bot(Request $request)
  {
    Notification::route('telegram', config('services.telegram-bot-api.chat_id'))->notify(new TelegramLocationNotification());
    return response()->json(['message' => 'Telegram notification sent successfully.']);
  }
}
