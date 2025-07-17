<?php

namespace App\Filament\Resources\ScheduledFileDeletionResource\Pages;

use App\Filament\Resources\ScheduledFileDeletionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditScheduledFileDeletion extends EditRecord
{
    protected static string $resource = ScheduledFileDeletionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
