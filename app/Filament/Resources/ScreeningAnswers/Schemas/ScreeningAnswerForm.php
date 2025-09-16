<?php

namespace App\Filament\Resources\ScreeningAnswers\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ScreeningAnswerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('screening_id')
                    ->relationship('screening', 'id')
                    ->searchable()
                    ->required(),
                Select::make('question_id')
                    ->relationship('question', 'question_text')
                    ->searchable()
                    ->required(),
                Textarea::make('answer')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }
}
