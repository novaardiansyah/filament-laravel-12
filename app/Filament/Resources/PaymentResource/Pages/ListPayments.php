<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;

class ListPayments extends ListRecords
{
  protected static string $resource = PaymentResource::class;

  protected function getHeaderActions(): array
  {
    return [
      Actions\CreateAction::make(),
    ];
  }

  public function getTabs(): array
  {
    return [
      'Pengeluaran' => Tab::make()
        ->modifyQueryUsing(fn ($query) => $query->where('type_id', 1)),
      'Pemasukan' => Tab::make()
        ->modifyQueryUsing(fn ($query) => $query->where('type_id', 2)),
      'Transfer' => Tab::make()
        ->modifyQueryUsing(fn ($query) => $query->where('type_id', 3)),
      'Tarik Tunai' => Tab::make()
        ->modifyQueryUsing(fn ($query) => $query->where('type_id', 4)),
      'All' => Tab::make(),
    ]; 
  }
}
