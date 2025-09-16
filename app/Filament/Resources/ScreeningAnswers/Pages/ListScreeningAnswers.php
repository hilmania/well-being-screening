<?php

namespace App\Filament\Resources\ScreeningAnswers\Pages;

use App\Filament\Resources\ScreeningAnswers\ScreeningAnswerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListScreeningAnswers extends ListRecords
{
    protected static string $resource = ScreeningAnswerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
