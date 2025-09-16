<?php

namespace App\Filament\Resources\VolunteersResponses\Pages;

use App\Filament\Resources\VolunteersResponses\VolunteersResponseResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListVolunteersResponses extends ListRecords
{
    protected static string $resource = VolunteersResponseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
