<?php

namespace App\Filament\Resources\WellBeingScreenings\Pages;

use App\Filament\Resources\WellBeingScreenings\WellBeingScreeningResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ViewWellBeingScreening extends ViewRecord
{
    protected static string $resource = WellBeingScreeningResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user.name')
                    ->label('Nama Responden')
                    ->disabled(),
                TextInput::make('user.email')
                    ->label('Email Responden')
                    ->disabled(),
                TextInput::make('screening_date')
                    ->label('Tanggal Screening')
                    ->disabled(),
                TextInput::make('score')
                    ->label('Skor')
                    ->disabled(),
                Textarea::make('result')
                    ->label('Hasil')
                    ->disabled(),
            ]);
    }
}
