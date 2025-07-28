<?php

namespace App\Filament\Resources\BillingMasterResource\Pages;

use App\Filament\Resources\BillingMasterResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBillingMaster extends EditRecord
{
    protected static string $resource = BillingMasterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
