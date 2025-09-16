<?php

namespace App\Filament\Resources\ScreeningQuestions\Pages;

use App\Filament\Resources\ScreeningQuestions\ScreeningQuestionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListScreeningQuestions extends ListRecords
{
    protected static string $resource = ScreeningQuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
