<?php

namespace App\Filament\Resources\PsychologistResponses;

use App\Filament\Resources\PsychologistResponses\Pages\CreatePsychologistResponse;
use App\Filament\Resources\PsychologistResponses\Pages\EditPsychologistResponse;
use App\Filament\Resources\PsychologistResponses\Pages\ListPsychologistResponses;
use App\Filament\Resources\PsychologistResponses\Schemas\PsychologistResponseForm;
use App\Filament\Resources\PsychologistResponses\Tables\PsychologistResponsesTable;
use App\Models\PsychologistResponse;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PsychologistResponseResource extends Resource
{
    protected static ?string $model = PsychologistResponse::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string | UnitEnum | null $navigationGroup = 'Psychologist Management';

    public static function form(Schema $schema): Schema
    {
        return PsychologistResponseForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PsychologistResponsesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPsychologistResponses::route('/'),
            'create' => CreatePsychologistResponse::route('/create'),
            'edit' => EditPsychologistResponse::route('/{record}/edit'),
        ];
    }
}
