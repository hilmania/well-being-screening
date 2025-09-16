<?php

namespace App\Filament\Resources\WellBeingScreenings\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class WellBeingScreeningForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->required(),
                DatePicker::make('screening_date')
                    ->required()
                    ->default(now()),
                TextInput::make('score')
                    ->numeric()
                    ->step(1),
                Textarea::make('result')
                    ->columnSpanFull(),
            ]);
    }
}
