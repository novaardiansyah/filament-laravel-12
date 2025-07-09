<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use App\Models\PaymentSummary;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;

class EditPayment extends EditRecord
{
  protected static string $resource = PaymentResource::class;

  #[On('refreshForm')]
  public function refreshForm(): void
  {
    parent::refreshFormData(array_keys($this->record->toArray()));
  }

  protected function getRedirectUrl(): string
  {
    $resource = static::getResource();
    return $resource::getUrl('index');
  }

  protected function mutateFormDataBeforeFill(array $data): array
  {
    $record = $this->record;
    $data['payment_account_deposit'] = toIndonesianCurrency($record->payment_account->deposit);
    return $data;
  }

  protected function mutateFormDataBeforeSave(array $data): array
  {
    $record = $this->record;

    // * Jika memiliki items, proses ini akan dilakukan di ItemsRelationManager
    if (!$record->has_items) {
      $amount = $data['amount'];

      // ? Pengeluaran
      if ($record->type_id == 1) {
        $data['expense'] = $amount;
      } else if ($record->type_id == 2) {
        // ? Pemasukan
        $data['income'] = $amount;
      }
    }

    return $data;
  }

  protected function beforeSave(): void
  {
    $record = $this->record;
    $data = $this->data;

    // * Jika memiliki items, proses ini akan dilakukan di ItemsRelationManager
    if (!$record->has_items) {
      $is_scheduled = $record->is_scheduled ?? false;
      $amount = intval($data['amount']);

      if ($record->type_id == 1 || $record->type_id == 2) {
        // ? Pengeluaran / Pemasukan
        $adjustment = ($record->type_id == 1) ? +$record->amount : -$record->amount;
        $depositChange = ($record->payment_account->deposit + $adjustment);

        if ($depositChange < $amount && $depositChange != 0) {
          $this->_error('Saldo akun kas tidak mencukupi!');
        }

        if ($record->type_id == 1) {
          $amount = -$amount;
        }
        $depositChange = $depositChange + $amount;
        
        if (!$is_scheduled) {
          $record->payment_account->update([
            'deposit' => $depositChange
          ]);
        }
      } else if ($record->type_id == 3 || $record->type_id == 4) {
        // ? Transfer / Tarik tunai

        // ! Ambil saldo dari akun tujuan lalu kembalikan ke akun asal
        $saldo_tujuan = $record->payment_account_to->deposit + $amount - $record->amount;
        $saldo_asal = $record->payment_account->deposit + $record->amount;

        if ($saldo_asal < $amount) {
          $this->_error('Saldo akun kas tidak mencukupi!');
        }

        if (!$is_scheduled) {
          $record->payment_account->update([
            'deposit' => $saldo_asal - $amount
          ]);

          $record->payment_account_to->update([
            'deposit' => $saldo_tujuan
          ]);
        }
      } else {
        // ! NO ACTION
        $this->_error('Tipe transaksi tidak valid.');
      }
    }

    // ! See if there are any changes to the attachments
    $removedAttachments = array_diff($record->attachments, $data['attachments']);

    // ? Has removed attachments
    if (!empty($removedAttachments)) {
      foreach ($removedAttachments as $attachment) {
        // ? Doesnt exist
        if (!Storage::disk('public')->exists($attachment))
          continue;

        // ! Delete attachment
        Storage::disk('public')->delete($attachment);
      }
    }
  }

  protected function afterSave(): void
  {
    $period = now()->translatedFormat('mY');
    PaymentSummary::setSync($period);
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
