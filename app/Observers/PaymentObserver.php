<?php

namespace App\Observers;

use App\Mail\PaymentAccountResource\DailySpendingMail;
use App\Models\EmailLog;
use App\Models\Payment;
use App\Models\Setting;
use Illuminate\Support\Facades\Mail;

class PaymentObserver
{
  /**
   * Handle the Payment "created" event.
   */
  public function created(Payment $payment): void
  {
    $this->checkDailyLimit($payment);
  }

  /**
   * Handle the Payment "updated" event.
   */
  public function updated(Payment $payment): void
  {
    $this->checkDailyLimit($payment);
  }

  /**
   * Handle the Payment "deleted" event.
   */
  public function deleted(Payment $payment): void
  {
    //
  }

  /**
   * Handle the Payment "restored" event.
   */
  public function restored(Payment $payment): void
  {
    //
  }

  /**
   * Handle the Payment "force deleted" event.
   */
  public function forceDeleted(Payment $payment): void
  {
    //
  }

  protected function checkDailyLimit(Payment $payment)
  {
    $now = now()->format('Y-m-d H:i:s');

    if ($payment->type_id != 1) {
      return;
    }

    $todayTotal = Payment::where('type_id', 1)
      ->whereDate('created_at', now()->toDateString())
      ->sum('amount');
    
    $limit = Setting::where('key', 'daily_spending_notification')->first()?->value ?? 0;
    $limit = (int) preg_replace('/[^\d]/', '', $limit);
    
    if ($limit <= 0) {
      return; // ! Tidak ada limit yang ditetapkan
    }

    if ($todayTotal >= $limit) {
      // ! Cek apakah sudah kirim notifikasi hari ini
      $hasSentToday = EmailLog::where('name', 'daily_spending_notification')
        ->whereDate('created_at', now()->toDateString())
        ->exists();
      
      if ($hasSentToday) {
        return; // ! Sudah mengirim notifikasi hari ini
      }

      $data = [
        'email'         => config('app.author_email'),
        'subject'       => 'Notifikasi: Limit Pengeluaran Harian Telah Tercapai',
        'total_expense' => $todayTotal,
        'limit'         => $limit,
        'date'          => now()->translatedFormat('d F Y'),
        'log_name'      => 'daily_spending_notification',
        'created_at'    => $now,
      ];

      $mailObj = new DailySpendingMail($data);
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

      Mail::to($data['email'])->queue(new DailySpendingMail($data));
    }
  }
}
