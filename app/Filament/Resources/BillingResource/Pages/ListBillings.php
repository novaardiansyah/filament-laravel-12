<?php

namespace App\Filament\Resources\BillingResource\Pages;

use App\Filament\Resources\BillingResource;
use App\Models\BillingPeriod;
use App\Models\BillingStatus;
use Filament\Actions;
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
        ->modalWidth(MaxWidth::ThreeExtraLarge)
        ->mutateFormDataUsing(function (array $data): array {
          $billingPeriodId = BillingPeriod::BILLING_PERIODS[$data['billing_period_id']] ?? BillingPeriod::BILLING_PERIODS['Monthly'];

          $data['billing_period_id'] = $billingPeriodId;
          $data['billing_master_id'] = (int) $data['billing_master_id'];
          
          return $data;
        }),
    ];
  }

  public function getTabs(): array
  {
    return [
      'All' => Tab::make(),
      BillingStatus::getName(BillingStatus::PENDING) => Tab::make()
        ->modifyQueryUsing(fn ($query) => $query->where('billing_status_id', BillingStatus::PENDING)),
      BillingStatus::getName(BillingStatus::PAID) => Tab::make()
        ->modifyQueryUsing(fn ($query) => $query->where('billing_status_id', BillingStatus::PAID)),
      BillingStatus::getName(BillingStatus::FAILED) => Tab::make() 
        ->modifyQueryUsing(fn ($query) => $query->where('billing_status_id', BillingStatus::FAILED)),
    ]; 
  }
}
