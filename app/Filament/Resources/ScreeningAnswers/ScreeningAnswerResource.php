<?php

namespace App\Filament\Resources\ScreeningAnswers;

use App\Filament\Resources\ScreeningAnswers\Pages\CreateScreeningAnswer;
use App\Filament\Resources\ScreeningAnswers\Pages\EditScreeningAnswer;
use App\Filament\Resources\ScreeningAnswers\Pages\ListScreeningAnswers;
use App\Filament\Resources\ScreeningAnswers\Schemas\ScreeningAnswerForm;
use App\Filament\Resources\ScreeningAnswers\Tables\ScreeningAnswersTable;
use App\Models\ScreeningAnswer;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ScreeningAnswerResource extends Resource
{
    protected static ?string $model = ScreeningAnswer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    // protected static string | UnitEnum | null $navigationGroup = 'References';

    protected static ?string $modelLabel = 'Jawaban Responden';

    protected static ?string $pluralModelLabel = 'Jawaban Responden';


    public static function form(Schema $schema): Schema
    {
        return ScreeningAnswerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ScreeningAnswersTable::configure($table);
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
            'index' => ListScreeningAnswers::route('/'),
            'create' => CreateScreeningAnswer::route('/create'),
            'edit' => EditScreeningAnswer::route('/{record}/edit'),
        ];
    }
}
