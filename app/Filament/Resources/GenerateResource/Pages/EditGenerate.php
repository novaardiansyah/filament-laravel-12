<?php

namespace App\Filament\Resources\GenerateResource\Pages;

use App\Filament\Resources\GenerateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGenerate extends EditRecord
{
    protected static string $resource = GenerateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
