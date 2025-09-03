<?php

namespace App\Filament\Resources\ShortUrlResource\Pages;

use App\Filament\Resources\ShortUrlResource;
use Filament\Resources\Pages\CreateRecord;

class CreateShortUrl extends CreateRecord
{
  protected static string $resource = ShortUrlResource::class;

  protected function getRedirectUrl(): string
  {
    $resource = static::getResource();
    return $resource::getUrl('index');
  }
}
