<?php

namespace App\Filament\Resources\ScreeningQuestions\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ScreeningQuestionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('question_text')
                    ->label('Pertanyaan')
                    ->required()
                    ->columnSpanFull()
                    ->rows(3),

                Select::make('question_type')
                    ->label('Tipe Pertanyaan')
                    ->options([
                        'likert' => 'Likert Scale (1-5)',
                        'text' => 'Text Input (Open Question)',
                    ])
                    ->required()
                    ->default('likert')
                    ->live()
                    ->columnSpanFull(),

                TextInput::make('placeholder')
                    ->label('Placeholder Text')
                    ->placeholder('Contoh: Jelaskan perasaan Anda...')
                    ->columnSpanFull()
                    ->visible(fn ($get) => $get('question_type') === 'text')
                    ->helperText('Text yang akan muncul sebagai placeholder di input field (opsional)'),

                Select::make('group_name')
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
                    ])
                    ->required()
                    ->default('basic_assessment')
                    ->searchable(),

                TextInput::make('order')
                    ->label('Urutan')
                    ->numeric()
                    ->default(0)
                    ->helperText('Angka untuk menentukan urutan tampil (semakin kecil semakin awal)'),

                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true)
                    ->helperText('Hanya pertanyaan yang aktif yang akan ditampilkan di screening'),
            ]);
    }
}
