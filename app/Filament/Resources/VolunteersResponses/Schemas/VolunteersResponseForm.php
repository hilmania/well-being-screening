<?php

namespace App\Filament\Resources\VolunteersResponses\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;
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

                // Tampilkan file yang sudah diupload jika ada
                Placeholder::make('current_attachment_display')
                    ->label('File Yang Sudah Diupload')
                    ->content(function ($record) {
                        if ($record && $record->attachment) {
                            $filename = basename($record->attachment);
                            $url = route('download.volunteer-attachment', ['filename' => $filename]);
                            $fileExtension = strtolower(pathinfo($record->attachment, PATHINFO_EXTENSION));

                            // Cek ukuran file jika memungkinkan
                            $filePath = storage_path('app/public/' . $record->attachment);
                            $fileSize = '';
                            if (file_exists($filePath)) {
                                $bytes = filesize($filePath);
                                if ($bytes >= 1024 * 1024) {
                                    $fileSize = ' • ' . round($bytes / (1024 * 1024), 2) . ' MB';
                                } else {
                                    $fileSize = ' • ' . round($bytes / 1024, 2) . ' KB';
                                }
                            }

                            return new \Illuminate\Support\HtmlString(
                                "<div class='flex items-center gap-3 p-4 bg-green-50 border border-green-200 rounded-lg'>
                                    <div class='flex-1 min-w-0'>
                                        <p class='text-sm font-medium text-green-900 truncate' title='{$filename}'>{$filename}</p>
                                        <p class='text-xs text-green-700'>File tersedia{$fileSize} • Klik tombol untuk download</p>
                                    </div>
                                    <div class='flex items-center gap-2'>
                                        <a href='{$url}' target='_blank'
                                           class='inline-flex items-center px-3 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-wide hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 transition ease-in-out duration-150'>
                                            Download
                                        </a>
                                        <span class='inline-flex items-center px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full'>
                                            {$fileExtension}
                                        </span>
                                    </div>
                                </div>"
                            );
                        }
                        return new \Illuminate\Support\HtmlString(
                            "<div class='flex items-center gap-3 p-4 bg-gray-50 border border-gray-200 rounded-lg'>
                                <div class='flex-1 min-w-0'>
                                    <p class='text-sm text-gray-600 font-medium'>Belum ada file yang diupload</p>
                                    <p class='text-xs text-gray-500'>Upload file CSV EEG di bagian bawah untuk menambahkan lampiran</p>
                                </div>
                                <span class='inline-flex items-center px-2 py-1 bg-gray-100 text-gray-600 text-xs font-medium rounded-full'>
                                    Kosong
                                </span>
                            </div>"
                        );
                    })
                    ->visible(fn ($record) => $record !== null) // Hanya tampil saat editing
                    ->columnSpanFull(),

                FileUpload::make('attachment')
                    ->label(fn ($record) => $record ? 'Ganti Lampiran File (Opsional)' : 'Lampiran File')
                    ->acceptedFileTypes(['text/csv', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                    ->maxSize(40960) // 40MB in kilobytes to be safe
                    ->helperText(fn ($record) => $record
                        ? 'Upload file baru untuk mengganti file yang sudah ada. Biarkan kosong jika tidak ingin mengubah. Maksimal ukuran: 40MB'
                        : 'Upload file CSV dari Alat EEG. Maksimal ukuran: 40MB'
                    )
                    ->directory('volunteer-attachments')
                    ->disk('public')
                    ->visibility('private')
                    ->previewable(false)
                    ->nullable()
                    ->columnSpanFull(),
            ]);
    }
}
