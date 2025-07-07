<?php

namespace App\Filament\Resources\PaymentSummaryResource\Pages;

use App\Filament\Resources\PaymentSummaryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPaymentSummary extends EditRecord
{
  protected static string $resource = PaymentSummaryResource::class;

  protected function mutateFormDataBeforeFill(array $data): array
  {
    $period = $data['period'] ?? null;

    if ($period) {
      $data['month'] = (int) substr($period, 0, 2);
      $data['year']  = (int) substr($period, 2, 4);
    }

    return $data;
  }

  protected function mutateFormDataBeforeSave(array $data): array
  {
    $year  = $data['year'];
    $month = $data['month'];

    if ($month < 10) $month = "0{$month}";
    $data['period'] = "{$month}{$year}";

    return $data;
  }
}
