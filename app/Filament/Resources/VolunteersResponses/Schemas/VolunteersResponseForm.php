<?php

namespace App\Filament\Resources\VolunteersResponses\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class VolunteersResponseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('screening_id')
                    ->relationship('screening', 'id')
                    ->searchable()
                    ->required(),
                Select::make('volunteer_id')
                    ->relationship('volunteer', 'name')
                    ->searchable()
                    ->required(),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }
}
