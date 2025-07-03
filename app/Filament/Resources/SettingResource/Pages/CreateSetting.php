<?php

namespace App\Filament\Resources\SettingResource\Pages;

use App\Filament\Resources\SettingResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSetting extends CreateRecord
{
  protected static string $resource = SettingResource::class;

  protected function mutateFormDataBeforeCreate(array $data): array
  {
    if ($data['has_options'] ?? false) {
      $data['value'] = $data['value_option'];
    }

    $options = $data['options'] ?? [];

    if (is_string($options)) {
      $options = array_map('trim', explode(',', $options));
      sort($options);
      $options = implode(',', $options);
      $data['options'] = $options;
    }

    return $data;
  }

  protected function getRedirectUrl(): string
  {
    $resource = static::getResource();
    return $resource::getUrl('index');
  }
}
