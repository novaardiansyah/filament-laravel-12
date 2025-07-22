<?php

namespace App\Jobs\PaymentResource;

use App\Services\PaymentService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class MakePdfJob implements ShouldQueue
{
  use Queueable;

  /**
   * Create a new job instance.
   */
  public function __construct(public array $data = [])
  {
    //
  }

  /**
   * Execute the job.
   */
  public function handle(): void
  {
    \Log::info('['. __METHOD__.':'.__LINE__ .']: Make PDF Job process started');
    
    $send = array_merge([
      'filename' => 'weekly-payment-report',
      'title'    => 'Laporan keuangan mingguan',
    ], $this->data);

    PaymentService::make_pdf($send);

    \Log::info('['. __METHOD__.':'.__LINE__ .']: Make PDF Job executed successfully');
  }
}
