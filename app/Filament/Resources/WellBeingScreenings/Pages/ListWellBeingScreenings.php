<?php

namespace App\Filament\Resources\WellBeingScreenings\Pages;

use App\Filament\Resources\WellBeingScreenings\WellBeingScreeningResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWellBeingScreenings extends ListRecords
{
    protected static string $resource = WellBeingScreeningResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
