<?php

namespace App\Filament\Resources\EmailLogResource\Pages;

use App\Filament\Resources\EmailLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;

class ListEmailLogs extends ListRecords
{
  protected static string $resource = EmailLogResource::class;

  protected function getHeaderActions(): array
  {
    return [
      // Actions\CreateAction::make(),
    ];
  }

  public function getTabs(): array
  {
    return [
      'All' => Tab::make(),
      'Pending' => Tab::make()
        ->modifyQueryUsing(fn ($query) => $query->where('status_id', 2)),
      'Success' => Tab::make()
        ->modifyQueryUsing(fn ($query) => $query->where('status_id', 3)),
      'Failed' => Tab::make()
        ->modifyQueryUsing(fn ($query) => $query->where('status_id', 4)),
    ]; 
  }
}
