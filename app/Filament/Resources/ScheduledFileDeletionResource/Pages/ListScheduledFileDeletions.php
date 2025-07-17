<?php

namespace App\Filament\Resources\ScheduledFileDeletionResource\Pages;

use App\Filament\Resources\ScheduledFileDeletionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListScheduledFileDeletions extends ListRecords
{
    protected static string $resource = ScheduledFileDeletionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
