<?php

namespace App\Filament\Resources\VolunteersResponses\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use App\Models\WellBeingScreening;
use App\Models\VolunteersResponse;

class VolunteersResponseForm
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
                TextInput::make('relawan_name')
                    ->label('Nama Relawan')
                    ->disabled()
                    ->dehydrated(false)
                    ->afterStateHydrated(function ($component, $state, $record) {
                        if ($record && $record->volunteer) {
                            $component->state($record->volunteer->name);
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
                        $handledScreeningIds = VolunteersResponse::pluck('screening_id')->toArray();
                        return WellBeingScreening::whereNotIn('id', $handledScreeningIds)
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
                    ->helperText('Pilih screening yang belum ditangani oleh relawan')
                    ->placeholder('Pilih screening...')
                    ->disabled(fn ($record) => $record !== null), // Disable jika editing
                Select::make('volunteer_id')
                    ->label('Relawan')
                    ->relationship('volunteer', 'name')
                    ->searchable()
                    ->required()
                    ->placeholder('Pilih relawan...')
                    ->disabled(fn ($record) => $record !== null) // Disable jika editing
                    ->visible(fn ($record) => $record === null), // Hanya tampil saat creating
                Textarea::make('notes')
                    ->label('Catatan Relawan')
                    ->rows(4)
                    ->placeholder('Masukkan catatan, saran, atau rekomendasi untuk klien...')
                    ->helperText('Berikan catatan yang membantu untuk follow-up dengan klien')
                    ->columnSpanFull(),
                FileUpload::make('attachment')
                    ->label('Lampiran File')
                    ->acceptedFileTypes(['text/csv', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                    ->maxSize(10240) // 10MB
                    ->helperText('Upload file CSV atau Excel. Maksimal ukuran: 10MB')
                    ->directory('volunteer-attachments')
                    ->disk('public')
                    ->visibility('private')
                    ->previewable(false)
                    ->nullable()
                    ->columnSpanFull(),
            ]);
    }
}
