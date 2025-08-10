<?php

namespace App\Filament\Resources\NoteResource\Pages;

use App\Filament\Resources\NoteResource;
use App\Models\Note;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\MaxWidth;
use Filament\Resources\Components\Tab;

class ListNotes extends ListRecords
{
  protected static string $resource = NoteResource::class;

  protected function getHeaderActions(): array
  {
    return [
      Actions\CreateAction::make()
        ->modalWidth(MaxWidth::ThreeExtraLarge)
        ->mutateFormDataUsing(fn (array $data, Actions\CreateAction $action) => NoteResource::getMutateFormData($data, $action))
    ];
  }

  public function getTabs(): array
  {
    return [
      'All' => Tab::make(),
      'Pending' => Tab::make()
        ->modifyQueryUsing(fn ($query) => $query->where('notification_at', '>', now())),
      'Completed' => Tab::make()
        ->modifyQueryUsing(fn ($query) => $query->where('notification_at', '<=', now())),
      'Deleted' => Tab::make()
        ->modifyQueryUsing(fn ($query) => $query->onlyTrashed()),
    ]; 
  }
}
