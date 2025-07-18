<?php

namespace App\Jobs\UserResource;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Database\Eloquent\Model;

class MakePdfJob implements ShouldQueue
{
  use Queueable;

  /**
   * Create a new job instance.
   */
  public function __construct(
    public Model $user
  ) { }

  /**
   * Execute the job.
   */
  public function handle(): bool
  {
    $now = now()->translatedFormat('d/m/Y H:i');
    
    $mpdf     = new \Mpdf\Mpdf();
    $rowIndex = 1;

    $mpdf->WriteHTML(view('user-resource.make-pdf.header', [
      'now' => $now,
    ])->render());

    User::with(['roles:id,name'])->chunk(200, function ($users) use ($mpdf, &$rowIndex) {
      foreach ($users as $user) {
        $view = view('user-resource.make-pdf.body', [
          'item'      => $user,
          'loopIndex' => $rowIndex++,
        ])->render();

        $mpdf->WriteHTML($view);
      }
    });
    
    $result = makePdf($mpdf, 'User', $this->user);

    return $result;
  }
}
