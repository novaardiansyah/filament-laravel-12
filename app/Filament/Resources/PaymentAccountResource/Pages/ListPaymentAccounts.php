<?php

namespace App\Filament\Resources\PaymentAccountResource\Pages;

use App\Filament\Resources\PaymentAccountResource;
use App\Filament\Resources\PaymentAccountResource\Widgets\PaymentAccountStatsOverview;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPaymentAccounts extends ListRecords
{
  protected static string $resource = PaymentAccountResource::class;

  protected function getHeaderWidgets(): array
  {
    return [
      PaymentAccountStatsOverview::class,
    ];
  }

  protected function getHeaderActions(): array
  {
    return [
      Actions\CreateAction::make(),
    ];
  }
}
