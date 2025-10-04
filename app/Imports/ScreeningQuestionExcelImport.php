<?php

namespace App\Imports;

use App\Models\ScreeningQuestion;
use EightyNine\ExcelImport\ExcelImport;

class ScreeningQuestionExcelImport extends ExcelImport
{
    public static function getModel(): string
    {
        return ScreeningQuestion::class;
    }

    public static function getColumns(): array
    {
        return [
            ExcelImport::make('question_text')
                ->label('Pertanyaan')
                ->helperText('Teks pertanyaan (maksimal 1000 karakter)')
                ->required()
                ->rules('required|string|max:1000'),

            ExcelImport::make('question_type')
                ->label('Tipe Pertanyaan')
                ->helperText('Pilih "likert" atau "text"')
                ->required()
                ->rules('required|in:likert,text')
                ->options([
                    'likert' => 'Likert Scale (1-5)',
                    'text' => 'Text Input (Open Question)',
                ]),

            ExcelImport::make('placeholder')
                ->label('Placeholder')
                ->helperText('Placeholder untuk input text (opsional)')
                ->rules('nullable|string|max:255'),

            ExcelImport::make('group_name')
                ->label('Grup Pertanyaan')
                ->helperText('Pilih grup yang sesuai')
                ->required()
                ->rules('required|in:basic_assessment,mood_emotion,anxiety_stress,sleep_energy,social_support,coping_strategy,life_quality,trauma_history,future_goals,custom')
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

            ExcelImport::make('order')
                ->label('Urutan')
                ->helperText('Angka untuk menentukan urutan tampil')
                ->rules('nullable|integer|min:0')
                ->default(0),

            ExcelImport::make('is_active')
                ->label('Status Aktif')
                ->helperText('true/false, 1/0, ya/tidak')
                ->rules('nullable|boolean')
                ->default(true)
                ->mutateBeforeCreate(function ($value) {
                    if (is_string($value)) {
                        $value = strtolower(trim($value));
                        return in_array($value, ['true', '1', 'yes', 'ya', 'aktif', 'active']);
                    }
                    if (is_numeric($value)) {
                        return (bool) $value;
                    }
                    return (bool) $value;
                }),
        ];
    }

    public static function getOptionsFromColumns(): array
    {
        return [
            'question_type' => [
                'likert' => 'Likert Scale (1-5)',
                'text' => 'Text Input (Open Question)',
            ],
            'group_name' => [
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
            ],
        ];
    }
}
