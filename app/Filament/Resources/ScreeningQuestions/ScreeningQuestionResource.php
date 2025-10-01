<?php

namespace App\Filament\Resources\ScreeningQuestions;

use App\Filament\Resources\ScreeningQuestions\Pages\CreateScreeningQuestion;
use App\Filament\Resources\ScreeningQuestions\Pages\EditScreeningQuestion;
use App\Filament\Resources\ScreeningQuestions\Pages\ListScreeningQuestions;
use App\Filament\Resources\ScreeningQuestions\Schemas\ScreeningQuestionForm;
use App\Filament\Resources\ScreeningQuestions\Tables\ScreeningQuestionsTable;
use App\Models\ScreeningQuestion;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ScreeningQuestionResource extends Resource
{
    protected static ?string $model = ScreeningQuestion::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string | UnitEnum | null $navigationGroup = 'References';

    protected static ?string $recordTitleAttribute = 'Screening Question';

    public static function form(Schema $schema): Schema
    {
        return ScreeningQuestionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ScreeningQuestionsTable::configure($table);
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
            'index' => ListScreeningQuestions::route('/'),
            'create' => CreateScreeningQuestion::route('/create'),
            'edit' => EditScreeningQuestion::route('/{record}/edit'),
        ];
    }
}
