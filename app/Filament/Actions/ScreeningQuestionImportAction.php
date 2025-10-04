<?php

namespace App\Filament\Actions;

use App\Models\ScreeningQuestion;
use EightyNine\ExcelImport\ExcelImportAction;

class ScreeningQuestionImportAction extends ExcelImportAction
{
    public static function getDefaultName(): ?string
    {
        return 'screeningQuestionImport';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Import Excel')
            ->modalHeading('Import Screening Questions')
            ->modalSubheading('Upload file Excel untuk mengimport pertanyaan secara batch')
            ->modalDescription('Pastikan file Excel sesuai dengan format template yang disediakan')
            ->icon('heroicon-o-arrow-up-tray')
            ->color('success')
            ->uploadDisk('local')
            ->uploadDirectory('temp/imports')
            ->maxSize(10240) // 10MB
            ->acceptedFileTypes(['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
            ->use(
                model: ScreeningQuestion::class,
                fieldsToImport: [
                    'question_text' => [
                        'label' => 'Pertanyaan',
                        'required' => true,
                        'rules' => 'required|string|max:1000',
                        'helperText' => 'Teks pertanyaan (maksimal 1000 karakter)',
                    ],
                    'question_type' => [
                        'label' => 'Tipe Pertanyaan',
                        'required' => true,
                        'rules' => 'required|in:likert,text',
                        'helperText' => 'Pilih "likert" atau "text"',
                        'options' => [
                            'likert' => 'Likert Scale (1-5)',
                            'text' => 'Text Input (Open Question)',
                        ]
                    ],
                    'placeholder' => [
                        'label' => 'Placeholder',
                        'required' => false,
                        'rules' => 'nullable|string|max:255',
                        'helperText' => 'Placeholder untuk input text (opsional untuk tipe text)',
                    ],
                    'group_name' => [
                        'label' => 'Grup Pertanyaan',
                        'required' => true,
                        'rules' => 'required|in:basic_assessment,mood_emotion,anxiety_stress,sleep_energy,social_support,coping_strategy,life_quality,trauma_history,future_goals,custom',
                        'helperText' => 'Pilih grup yang sesuai',
                        'options' => [
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
                        ]
                    ],
                    'order' => [
                        'label' => 'Urutan',
                        'required' => false,
                        'rules' => 'nullable|integer|min:0',
                        'helperText' => 'Angka untuk menentukan urutan tampil (default: 0)',
                        'default' => 0,
                    ],
                    'is_active' => [
                        'label' => 'Status Aktif',
                        'required' => false,
                        'rules' => 'nullable|boolean',
                        'helperText' => 'true/false, 1/0, ya/tidak (default: true)',
                        'default' => true,
                    ],
                ]
            )
            ->sampleExcel(
                sampleData: [
                    [
                        'question_text' => 'Bagaimana perasaan Anda dalam 2 minggu terakhir?',
                        'question_type' => 'likert',
                        'placeholder' => '',
                        'group_name' => 'basic_assessment',
                        'order' => 1,
                        'is_active' => true,
                    ],
                    [
                        'question_text' => 'Ceritakan pengalaman yang membuat Anda merasa bahagia',
                        'question_type' => 'text',
                        'placeholder' => 'Jelaskan dengan detail pengalaman Anda...',
                        'group_name' => 'mood_emotion',
                        'order' => 2,
                        'is_active' => true,
                    ],
                    [
                        'question_text' => 'Seberapa sering Anda merasa cemas dalam seminggu terakhir?',
                        'question_type' => 'likert',
                        'placeholder' => '',
                        'group_name' => 'anxiety_stress',
                        'order' => 3,
                        'is_active' => true,
                    ],
                    [
                        'question_text' => 'Apa strategi yang Anda gunakan untuk mengatasi stress?',
                        'question_type' => 'text',
                        'placeholder' => 'Sebutkan strategi atau teknik yang biasa Anda lakukan...',
                        'group_name' => 'coping_strategy',
                        'order' => 4,
                        'is_active' => false,
                    ],
                ],
                fileName: 'template-screening-questions.xlsx'
            );
    }
}
