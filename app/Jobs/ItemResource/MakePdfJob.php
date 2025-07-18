<?php

namespace App\Jobs\ItemResource;

use App\Models\Item;
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
  ) {  }

  /**
   * Execute the job.
   */
  public function handle(): bool
  {
    $now = now()->translatedFormat('d/m/Y H:i');
    
    $mpdf     = new \Mpdf\Mpdf();
    $rowIndex = 1;

    $mpdf->WriteHTML(view('item-resource.make-pdf.header', [
      'now' => $now,
    ])->render());

    Item::with(['type:id,name'])->chunk(200, function ($items) use ($mpdf, &$rowIndex) {
      foreach ($items as $item) {
        $view = view('item-resource.make-pdf.body', [
          'item'      => $item,
          'loopIndex' => $rowIndex++,
        ])->render();

        $mpdf->WriteHTML($view);
      }
    });

    $result = makePdf($mpdf, 'Item', $this->user);

    return $result;
  }
}
