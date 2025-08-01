<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use App\Models\Payment;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreatePayment extends CreateRecord
{
  protected static string $resource = PaymentResource::class;

  protected function getRedirectUrl(): string
  {
    $resource = static::getResource();
    $record = $this->getRecord();

    if ($record->has_items)
      return $resource::getUrl('edit', ['record' => $record]);

    return $resource::getUrl('index');
  }

  protected function mutateFormDataBeforeCreate(array $data): array
  {
    $payment = new Payment();

    $mutate = $payment::mutateDataPayment($data);
    $data = $mutate['data'];

    if ($mutate['status'] == false) {
      $this->_error($mutate['message']);
    }

    return $data;
  }

  private function _error(string $message): void
  {
    Notification::make()
      ->danger()
      ->title('Proses gagal!')
      ->body($message)
      ->send();

    $this->halt();
  }
}
