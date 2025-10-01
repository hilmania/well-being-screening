<?php

namespace App\Filament\Resources\UnhandledScreeningResource\Pages;

use App\Filament\Resources\UnhandledScreeningResource;
use Filament\Resources\Pages\ListRecords;

class ListUnhandledScreenings extends ListRecords
{
    protected static string $resource = UnhandledScreeningResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Tidak ada create action karena ini read-only
        ];
    }
}
