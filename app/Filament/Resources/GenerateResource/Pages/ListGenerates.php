<?php

namespace App\Filament\Resources\GenerateResource\Pages;

use App\Filament\Resources\GenerateResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGenerates extends ListRecords
{
    protected static string $resource = GenerateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
