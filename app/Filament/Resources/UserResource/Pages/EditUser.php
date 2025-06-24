<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use STS\FilamentImpersonate\Pages\Actions\Impersonate;

class EditUser extends EditRecord
{
  protected static string $resource = UserResource::class;

  protected function getHeaderActions(): array
  {
    return [
      Actions\DeleteAction::make(),
      Impersonate::make()->record($this->getRecord())
    ];
  }

  protected function mutateFormDataBeforeSave(array $data): array
  {
    if (!$data['password']) unset($data['password']);
    return $data;
  }
}
