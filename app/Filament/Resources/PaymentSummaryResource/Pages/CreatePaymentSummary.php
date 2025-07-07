<?php

namespace App\Filament\Resources\PaymentSummaryResource\Pages;

use App\Filament\Resources\PaymentSummaryResource;
use App\Models\PaymentSummary;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreatePaymentSummary extends CreateRecord
{
  protected static string $resource = PaymentSummaryResource::class;

  protected function mutateFormDataBeforeCreate(array $data): array
  {
    $year  = $data['year'];
    $month = $data['month'];

    if ($month < 10) $month = "0{$month}";
    $period = "{$month}{$year}";

    $existing = PaymentSummary::where('period', $period)->first();

    if ($existing) {
      Notification::make()
        ->title('Gagal!')
        ->body('Periode summary ini sudah ada.')
        ->danger()
        ->send();

      $this->halt;
    }

    $data['code'] = getCode(10);
    $data['period'] = $period;

    return $data;
  }
}
