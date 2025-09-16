<?php

namespace App\Filament\Resources\PsychologistResponses\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class PsychologistResponseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('screening_id')
                    ->relationship('screening', 'id')
                    ->searchable()
                    ->required(),
                Select::make('psychologist_id')
                    ->relationship('psychologist', 'name')
                    ->searchable()
                    ->required(),
                Textarea::make('diagnosis')
                    ->columnSpanFull(),
                Textarea::make('recommendation')
                    ->columnSpanFull(),
            ]);
    }
}
