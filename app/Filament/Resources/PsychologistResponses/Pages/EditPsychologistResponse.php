<?php

namespace App\Filament\Resources\PsychologistResponses\Pages;

use App\Filament\Resources\PsychologistResponses\PsychologistResponseResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPsychologistResponse extends EditRecord
{
    protected static string $resource = PsychologistResponseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
