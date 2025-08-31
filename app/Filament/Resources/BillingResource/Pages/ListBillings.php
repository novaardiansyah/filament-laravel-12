<?php

namespace App\Filament\Resources\BillingResource\Pages;

use App\Filament\Resources\BillingResource;
use App\Models\Billing;
use App\Models\BillingStatus;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\MaxWidth;
use Filament\Resources\Components\Tab;

class ListBillings extends ListRecords
{
  protected static string $resource = BillingResource::class;

  protected function getHeaderActions(): array
  {
    return [
      Actions\CreateAction::make()
        ->modalWidth(MaxWidth::FourExtraLarge)
        ->mutateFormDataUsing(function (array $data): array {
          $exist = Billing::where('item_id', $data['item_id'])
            ->whereIn('billing_status_id', [
              BillingStatus::PENDING,
              BillingStatus::FAILED,
            ])
            ->exists();

          if ($exist) {
            Notification::make()
              ->title('Gagal membuat tagihan')
              ->body('Tagihan untuk produk & layanan ini sudah ada, dan belum dibayar.')
              ->danger()
              ->send();

            $this->halt();
          }

          $data['code'] = getCode('billing');
          return $data;
        }),
    ];
  }

  public function getTabs(): array
  {
    return [
      BillingStatus::getName(BillingStatus::PENDING) => Tab::make()
        ->modifyQueryUsing(fn ($query) => $query->where('billing_status_id', BillingStatus::PENDING)),
      BillingStatus::getName(BillingStatus::SCHEDULED) => Tab::make()
        ->modifyQueryUsing(fn ($query) => $query->where('billing_status_id', BillingStatus::SCHEDULED)),
      BillingStatus::getName(BillingStatus::PAID) => Tab::make()
        ->modifyQueryUsing(fn ($query) => $query->where('billing_status_id', BillingStatus::PAID)),
      BillingStatus::getName(BillingStatus::FAILED) => Tab::make() 
        ->modifyQueryUsing(fn ($query) => $query->where('billing_status_id', BillingStatus::FAILED)),
      'All' => Tab::make(),
    ]; 
  }
}
