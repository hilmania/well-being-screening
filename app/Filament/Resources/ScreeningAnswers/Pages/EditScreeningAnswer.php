<?php

namespace App\Filament\Resources\ScreeningAnswers\Pages;

use App\Filament\Resources\ScreeningAnswers\ScreeningAnswerResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditScreeningAnswer extends EditRecord
{
    protected static string $resource = ScreeningAnswerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
