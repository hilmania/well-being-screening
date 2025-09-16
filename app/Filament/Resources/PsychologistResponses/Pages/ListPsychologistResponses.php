<?php

namespace App\Filament\Resources\PsychologistResponses\Pages;

use App\Filament\Resources\PsychologistResponses\PsychologistResponseResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPsychologistResponses extends ListRecords
{
    protected static string $resource = PsychologistResponseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
