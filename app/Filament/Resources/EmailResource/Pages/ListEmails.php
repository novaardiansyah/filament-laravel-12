<?php

namespace App\Filament\Resources\EmailResource\Pages;

use App\Filament\Resources\EmailResource;
use App\Models\Email;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\MaxWidth;

class ListEmails extends ListRecords
{
  protected static string $resource = EmailResource::class;

  protected function getHeaderActions(): array
  {
    return [
      Actions\CreateAction::make()
        ->label('Buat Email')
        ->modalWidth(MaxWidth::ThreeExtraLarge)
        ->mutateFormDataUsing(function (array $data): array{
          $data['code'] = getCode('email');
          return $data;
        })
        ->after(function (array $data, Email $record) {
          $saveAsDraft = (bool) $data['save_as_draft'];
          if ($saveAsDraft) return;

          $record->sendEmail();
        }),
    ];
  }
}
