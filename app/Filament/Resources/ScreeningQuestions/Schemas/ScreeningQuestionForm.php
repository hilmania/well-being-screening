<?php

namespace App\Filament\Resources\ScreeningQuestions\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ScreeningQuestionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('question_text')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }
}
