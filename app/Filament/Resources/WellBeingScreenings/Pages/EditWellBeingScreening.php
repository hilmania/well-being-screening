<?php

namespace App\Filament\Resources\WellBeingScreenings\Pages;

use App\Filament\Resources\WellBeingScreenings\WellBeingScreeningResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWellBeingScreening extends EditRecord
{
    protected static string $resource = WellBeingScreeningResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
