<?php

namespace App\Filament\Resources\ItemResource\Pages;

use App\Filament\Resources\ItemResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditItem extends EditRecord
{
  protected static string $resource = ItemResource::class;

  protected function getRedirectUrl(): ?string
  {
    return $this->getResource()::getUrl('index');
  }
}
