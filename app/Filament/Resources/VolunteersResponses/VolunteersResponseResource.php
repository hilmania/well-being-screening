<?php

namespace App\Filament\Resources\VolunteersResponses;

use App\Filament\Resources\VolunteersResponses\Pages\CreateVolunteersResponse;
use App\Filament\Resources\VolunteersResponses\Pages\EditVolunteersResponse;
use App\Filament\Resources\VolunteersResponses\Pages\ListVolunteersResponses;
use App\Filament\Resources\VolunteersResponses\Schemas\VolunteersResponseForm;
use App\Filament\Resources\VolunteersResponses\Tables\VolunteersResponsesTable;
use App\Models\VolunteersResponse;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class VolunteersResponseResource extends Resource
{
    protected static ?string $model = VolunteersResponse::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string | UnitEnum | null $navigationGroup = 'Relawan';

    protected static ?string $navigationLabel = 'Respon Relawan';

    protected static ?string $modelLabel = 'Respon Relawan';

    protected static ?string $pluralModelLabel = 'Respon Relawan';

    public static function form(Schema $schema): Schema
    {
        return VolunteersResponseForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VolunteersResponsesTable::configure($table);
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
            'index' => ListVolunteersResponses::route('/'),
            'create' => CreateVolunteersResponse::route('/create'),
            'edit' => EditVolunteersResponse::route('/{record}/edit'),
        ];
    }
}
