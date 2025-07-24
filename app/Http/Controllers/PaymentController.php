<?php

namespace App\Http\Controllers;

use App\Models\Payment;

class PaymentController extends Controller
{
  public static function scheduledPayment(): array
  {
    $today = now()->format('Y-m-d');
    $tomorrow = now()->addDay()->format('Y-m-d');

    $scheduledPayments = Payment::with(['payment_account:id,name,deposit', 'payment_account_to:id,name,deposit'])->where('is_scheduled', true)
      ->whereBetween('date', [$today, $tomorrow])
      ->get();

    if ($scheduledPayments->isEmpty()) {
      return ['message' => 'No scheduled payments found for today.'];
    }

    $scheduledPayments->each(function ($payment) {
      $payment->is_scheduled = false;
      $payment->save();

      if ($payment->type_id === 1) {
        // ! Pengeluaran
        $payment->payment_account->deposit -= $payment->amount;
      } else if ($payment->type_id === 2) {
        // ! Pemasukan
        $payment->payment_account->deposit += $payment->amount;
      }

      if ($payment->payment_account_to && ($payment->type_id === 3 || $payment->type_id === 4)) {
        // ! Transfer (3) / Tarik Tunai (4)
        $payment->payment_account->deposit -= $payment->amount;
        $payment->payment_account_to->deposit += $payment->amount;
      }

      if ($payment->payment_account->isDirty()) {
        $payment->payment_account->save();
      }

      if ($payment->payment_account_to && $payment->payment_account_to->isDirty()) {
        $payment->payment_account_to->save();
      }
    });

    return ['message' => 'Scheduled payments processed successfully.'];
  }
}
