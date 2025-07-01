<?php

namespace App\Filament\Resources\SettingResource\Pages;

use App\Filament\Resources\SettingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSetting extends EditRecord
{
  protected static string $resource = SettingResource::class;

  protected function getHeaderActions(): array
  {
    return [
      Actions\DeleteAction::make(),
    ];
  }

  protected function mutateFormDataBeforeSave(array $data): array
  {
    if ($data['has_options']) {
      $data['value'] = $data['value_option'];
    }
    return $data;
  }

  protected function mutateFormDataBeforeFill(array $data): array
  {
    if ($data['has_options']) {
      $data['value_option'] = $data['value'];
    }
    return $data;
  }

  protected function getRedirectUrl(): ?string
  {
    return $this->getResource()::getUrl('edit', [
      'record' => $this->getRecord(),
    ]);
  }
}
