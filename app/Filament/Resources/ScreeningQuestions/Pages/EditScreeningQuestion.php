<?php

namespace App\Filament\Resources\ScreeningQuestions\Pages;

use App\Filament\Resources\ScreeningQuestions\ScreeningQuestionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditScreeningQuestion extends EditRecord
{
    protected static string $resource = ScreeningQuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
