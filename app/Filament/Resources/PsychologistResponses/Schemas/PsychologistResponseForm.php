<?php

namespace App\Filament\Resources\PsychologistResponses\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use App\Models\WellBeingScreening;
use App\Models\PsychologistResponse;

class PsychologistResponseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('responden_name')
                    ->label('Nama Responden')
                    ->disabled()
                    ->dehydrated(false)
                    ->afterStateHydrated(function ($component, $state, $record) {
                        if ($record && $record->screening && $record->screening->user) {
                            $component->state($record->screening->user->name);
                        }
                    }),
                TextInput::make('psikolog_name')
                    ->label('Nama Psikolog')
                    ->disabled()
                    ->dehydrated(false)
                    ->afterStateHydrated(function ($component, $state, $record) {
                        if ($record && $record->psychologist) {
                            $component->state($record->psychologist->name);
                        }
                    }),
                Select::make('screening_id')
                    ->label('Screening')
                    ->options(function ($record) {
                        // Jika editing, tampilkan screening yang sedang dipilih
                        if ($record) {
                            return [$record->screening_id => "#{$record->screening_id} - {$record->screening->user->name} - Skor: {$record->screening->score}"];
                        }

                        // Jika creating, tampilkan screening yang belum ditangani
                        $handledScreeningIds = PsychologistResponse::pluck('screening_id')->toArray();
                        return WellBeingScreening::whereNotIn('id', $handledScreeningIds)
                            ->where('score', '>=', 60) // Hanya skor medium ke atas
                            ->with('user')
                            ->get()
                            ->mapWithKeys(function ($screening) {
                                $userInfo = $screening->user ? $screening->user->name : 'No User';
                                $scoreInfo = "Skor: {$screening->score}";
                                $dateInfo = $screening->screening_date ? $screening->screening_date->format('d/m/Y') : 'No Date';

                                return [
                                    $screening->id => "#{$screening->id} - {$userInfo} - {$scoreInfo} - {$dateInfo}"
                                ];
                            });
                    })
                    ->searchable()
                    ->required()
                    ->helperText('Pilih screening yang belum ditangani oleh psikolog')
                    ->placeholder('Pilih screening...')
                    ->disabled(fn ($record) => $record !== null), // Disable jika editing
                Select::make('psychologist_id')
                    ->label('Psikolog')
                    ->relationship('psychologist', 'name')
                    ->searchable()
                    ->required()
                    ->placeholder('Pilih psikolog...')
                    ->disabled(fn ($record) => $record !== null) // Disable jika editing
                    ->visible(fn ($record) => $record === null), // Hanya tampil saat creating
                Textarea::make('diagnosis')
                    ->label('Diagnosis')
                    ->rows(4)
                    ->placeholder('Masukkan diagnosis berdasarkan hasil screening...')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('recommendation')
                    ->label('Rekomendasi')
                    ->rows(4)
                    ->placeholder('Masukkan rekomendasi penanganan untuk klien...')
                    ->required()
                    ->columnSpanFull(),
                // FileUpload::make('attachment')
                //     ->label('Lampiran File')
                //     ->acceptedFileTypes(['text/csv', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/pdf'])
                //     ->maxSize(10240) // 10MB
                //     ->helperText('Upload file CSV, Excel, atau PDF. Maksimal ukuran: 10MB')
                //     ->directory('psychologist-attachments')
                //     ->disk('public')
                //     ->visibility('private')
                //     ->previewable(false)
                //     ->nullable()
                //     ->columnSpanFull(),
            ]);
    }
}
