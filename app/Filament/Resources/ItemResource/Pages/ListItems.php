<?php

namespace App\Filament\Resources\ItemResource\Pages;

use App\Filament\Resources\ItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;

class ListItems extends ListRecords
{
  protected static string $resource = ItemResource::class;

  protected function getHeaderActions(): array
  {
    return [
      Actions\CreateAction::make(),
    ];
  }

  public function getTabs(): array
  {
    return [
      'Produk' => Tab::make()
        ->modifyQueryUsing(fn ($query) => $query->where('type_id', 1)),
      'Layanan' => Tab::make()
        ->modifyQueryUsing(fn ($query) => $query->where('type_id', 2)),
      'Deleted' => Tab::make()
        ->modifyQueryUsing(fn ($query) => $query->onlyTrashed()),
      'All' => Tab::make(),
    ]; 
  }
}
