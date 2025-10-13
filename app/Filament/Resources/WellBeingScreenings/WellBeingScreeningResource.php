<?php

namespace App\Filament\Resources\WellBeingScreenings;

use App\Filament\Resources\WellBeingScreenings\Pages\CreateWellBeingScreening;
use App\Filament\Resources\WellBeingScreenings\Pages\EditWellBeingScreening;
use App\Filament\Resources\WellBeingScreenings\Pages\ListWellBeingScreenings;
use App\Filament\Resources\WellBeingScreenings\Pages\ViewWellBeingScreening;
use App\Filament\Resources\WellBeingScreenings\Schemas\WellBeingScreeningForm;
use App\Filament\Resources\WellBeingScreenings\Tables\WellBeingScreeningsTable;
use App\Models\WellBeingScreening;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WellBeingScreeningResource extends Resource
{
    protected static ?string $model = WellBeingScreening::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $modelLabel = 'Daftar Screening';

    protected static ?string $pluralModelLabel = 'Daftar Screening';


    public static function form(Schema $schema): Schema
    {
        return WellBeingScreeningForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WellBeingScreeningsTable::configure($table);
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
            'index' => ListWellBeingScreenings::route('/'),
            'create' => CreateWellBeingScreening::route('/create'),
            'view' => ViewWellBeingScreening::route('/{record}'),
            'edit' => EditWellBeingScreening::route('/{record}/edit'),
        ];
    }
}
