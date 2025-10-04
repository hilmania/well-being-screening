<?php

namespace App\Filament\Actions;

use App\Models\ScreeningQuestion;
use App\Imports\CustomScreeningQuestionImport;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class ScreeningQuestionImportAction extends Action
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
            ->form([
                FileUpload::make('file')
                    ->label('File Excel')
                    ->disk('local')
                    ->directory('livewire-tmp')
                    ->visibility('private')
                    ->maxSize(10240) // 10MB
                    ->acceptedFileTypes([
                        'application/vnd.ms-excel',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                    ])
                    ->required()
                    ->helperText('Upload file Excel dengan format yang sesuai template'),
            ])
            ->action(function (array $data) {
                try {
                    $filePath = $data['file'];
                    
                    // Define the fields to import
                    $fieldsToImport = [
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
                    ];
                    
                    // Import using custom import class
                    CustomScreeningQuestionImport::importFromFile(
                        $filePath,
                        ScreeningQuestion::class,
                        ['fieldsToImport' => $fieldsToImport],
                        []
                    );
                    
                    // Show success notification
                    Notification::make()
                        ->title('Import Berhasil')
                        ->body('Pertanyaan screening berhasil diimport dari file Excel.')
                        ->success()
                        ->send();
                        
                } catch (\Exception $e) {
                    // Show error notification
                    Notification::make()
                        ->title('Import Gagal')
                        ->body('Terjadi kesalahan saat mengimport file: ' . $e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
}