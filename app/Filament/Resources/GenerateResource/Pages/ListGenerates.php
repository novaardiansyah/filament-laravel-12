<?php

namespace App\Filament\Resources\GenerateResource\Pages;

use App\Filament\Resources\GenerateResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\MaxWidth;
use Str;
use Filament\Resources\Components\Tab;

class ListGenerates extends ListRecords
{
  protected static string $resource = GenerateResource::class;

  protected function getHeaderActions(): array
  {
    return [
      Actions\CreateAction::make()
        ->modalWidth(MaxWidth::FourExtraLarge)
        ->mutateFormDataUsing(function (array $data): array {
          $data['prefix'] .= '-';
          $data['alias'] = Str::slug($data['alias'], '_');
          return $data;
        }),
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
