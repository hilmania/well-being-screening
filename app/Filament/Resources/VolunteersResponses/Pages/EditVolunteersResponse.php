<?php

namespace App\Filament\Resources\VolunteersResponses\Pages;

use App\Filament\Resources\VolunteersResponses\VolunteersResponseResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditVolunteersResponse extends EditRecord
{
    protected static string $resource = VolunteersResponseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
