<?php

namespace App\Filament\Resources\BillingMasterResource\Pages;

use App\Filament\Resources\BillingMasterResource;
use App\Models\BillingMaster;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\MaxWidth;

class ListBillingMasters extends ListRecords
{
  protected static string $resource = BillingMasterResource::class;

  protected function getHeaderActions(): array
  {
    return [
      Actions\CreateAction::make()
        ->modalWidth(MaxWidth::ThreeExtraLarge)
        ->mutateFormDataUsing(function (array $data): array {
          $item_id = $data['item_id'] ?? -1;

          $existingBillingMaster = BillingMaster::where('item_id', $item_id)
            ->whereNull('deleted_at')
            ->first();

          if ($existingBillingMaster) {
            Notification::make()
              ->title('Gagal menambahkan tagihan')
              ->body('Produk & Layanan sudah ada di daftar tagihan')
              ->danger()
              ->send();

            $this->halt();
          }

          $data['code'] = getCode(11);
          return $data;
        }),
    ];
  }
}
