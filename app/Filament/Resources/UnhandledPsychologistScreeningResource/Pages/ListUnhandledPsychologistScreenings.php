<?php

namespace App\Filament\Resources\UnhandledPsychologistScreeningResource\Pages;

use App\Filament\Resources\UnhandledPsychologistScreeningResource;
use Filament\Resources\Pages\ListRecords;

class ListUnhandledPsychologistScreenings extends ListRecords
{
    protected static string $resource = UnhandledPsychologistScreeningResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Tidak ada actions karena ini read-only
        ];
    }
}
