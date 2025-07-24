<?php

namespace App\Jobs\PaymentResource;

use App\Http\Controllers\PaymentController;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ScheduledPaymentJob implements ShouldQueue
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
    \Log::info('['. __METHOD__.':'.__LINE__ .']: Scheduled Payment Job process started');
    $result = PaymentController::scheduledPayment();
    \Log::info('['. __METHOD__.':'.__LINE__ .']: Scheduled Payment Job executed successfully', $result);
  }
}
