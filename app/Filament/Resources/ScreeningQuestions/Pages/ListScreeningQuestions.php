<?php

namespace App\Filament\Resources\ScreeningQuestions\Pages;

use App\Filament\Actions\ScreeningQuestionImportAction;
use App\Filament\Resources\ScreeningQuestions\ScreeningQuestionResource;
use App\Models\ScreeningQuestion;
use EightyNine\ExcelImport\ExcelImportAction;
use Filament\Actions\CreateAction;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListScreeningQuestions extends ListRecords
{
    protected static string $resource = ScreeningQuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ScreeningQuestionImportAction::make(),
            CreateAction::make(),
        ];
    }
}
