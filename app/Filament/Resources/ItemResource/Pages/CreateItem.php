<?php

namespace App\Filament\Resources\ItemResource\Pages;

use App\Filament\Resources\ItemResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateItem extends CreateRecord
{
  protected static string $resource = ItemResource::class;

  protected function mutateFormDataBeforeCreate(array $data): array
  {
    $data['code'] = getCode(3);
    return $data;
  }

  protected function getRedirectUrl(): string
  {
    $resource = static::getResource();
    return $resource::getUrl('index');
  }
}
