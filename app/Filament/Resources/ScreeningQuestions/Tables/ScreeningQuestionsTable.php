<?php

namespace App\Filament\Resources\ScreeningQuestions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Table;

class ScreeningQuestionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order')
                    ->label('#')
                    ->sortable()
                    ->width(50),

                TextColumn::make('question_text')
                    ->label('Pertanyaan')
                    ->limit(40)
                    ->searchable(),

                BadgeColumn::make('question_type')
                    ->label('Tipe')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'likert' => 'Likert Scale',
                        'text' => 'Text Input',
                        default => $state,
                    })
                    ->colors([
                        'primary' => 'likert',
                        'success' => 'text',
                    ]),

                BadgeColumn::make('group_name')
                    ->label('Grup')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'basic_assessment' => 'Assessment Dasar',
                        'mood_emotion' => 'Mood & Emosi',
                        'anxiety_stress' => 'Kecemasan & Stress',
                        'sleep_energy' => 'Tidur & Energi',
                        'social_support' => 'Dukungan Sosial',
                        'coping_strategy' => 'Strategi Coping',
                        'life_quality' => 'Kualitas Hidup',
                        'trauma_history' => 'Riwayat Trauma',
                        'future_goals' => 'Tujuan & Harapan',
                        'custom' => 'Custom Group',
                        default => $state,
                    })
                    ->colors([
                        'warning' => 'basic_assessment',
                        'info' => 'mood_emotion',
                        'danger' => 'anxiety_stress',
                        'secondary' => 'sleep_energy',
                        'success' => 'social_support',
                        'primary' => 'coping_strategy',
                        'gray' => fn ($state) => in_array($state, ['life_quality', 'trauma_history', 'future_goals', 'custom']),
                    ]),

                BooleanColumn::make('is_active')
                    ->label('Aktif')
                    ->sortable(),

                TextColumn::make('placeholder')
                    ->label('Placeholder')
                    ->limit(20)
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('question_type')
                    ->label('Tipe Pertanyaan')
                    ->options([
                        'likert' => 'Likert Scale',
                        'text' => 'Text Input',
                    ]),

                \Filament\Tables\Filters\SelectFilter::make('group_name')
                    ->label('Grup Pertanyaan')
                    ->options([
                        'basic_assessment' => 'Assessment Dasar',
                        'mood_emotion' => 'Mood & Emosi',
                        'anxiety_stress' => 'Kecemasan & Stress',
                        'sleep_energy' => 'Tidur & Energi',
                        'social_support' => 'Dukungan Sosial',
                        'coping_strategy' => 'Strategi Coping',
                        'life_quality' => 'Kualitas Hidup',
                        'trauma_history' => 'Riwayat Trauma',
                        'future_goals' => 'Tujuan & Harapan',
                        'custom' => 'Custom Group',
                    ]),

                \Filament\Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif')
                    ->placeholder('Semua pertanyaan')
                    ->trueLabel('Hanya yang aktif')
                    ->falseLabel('Hanya yang non-aktif'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
