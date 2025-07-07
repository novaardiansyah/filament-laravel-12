<?php

namespace App\Filament\Resources\PaymentSummaryResource\Pages;

use App\Filament\Resources\PaymentSummaryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;

class ListPaymentSummaries extends ListRecords
{
  protected static string $resource = PaymentSummaryResource::class;

  protected function getHeaderActions(): array
  {
    return [
      Actions\CreateAction::make(),
    ];
  }

  public function getTabs(): array
  {
    return [
      'All' => Tab::make(),
      'Deleted' => Tab::make()
        ->modifyQueryUsing(fn ($query) => $query->onlyTrashed()),
    ]; 
  }
}
