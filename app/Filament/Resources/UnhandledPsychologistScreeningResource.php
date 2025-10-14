<?php

namespace App\Filament\Resources;

use App\Models\WellBeingScreening;
use App\Models\PsychologistResponse;
use App\Models\VolunteersResponse;
use App\Models\User;
use App\Services\TopoplotService;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Placeholder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\HtmlString;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Filament\Support\Icons\Heroicon;
use BackedEnum;
use UnitEnum;

class UnhandledPsychologistScreeningResource extends Resource
{
    protected static ?string $model = WellBeingScreening::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Screening Belum Ditangani';

    protected static ?string $modelLabel = 'Screening Belum Ditangani';

    protected static ?string $pluralModelLabel = 'Screening Belum Ditangani';

    protected static string | UnitEnum | null $navigationGroup = 'Psikolog';

    public static function getEloquentQuery(): Builder
    {
        // Mendapatkan ID screening yang sudah ditangani oleh psikolog
        $handledByPsychologistIds = PsychologistResponse::pluck('screening_id')->toArray();

        // Mendapatkan ID screening yang sudah ditangani oleh relawan
        $handledByVolunteerIds = VolunteersResponse::pluck('screening_id')->toArray();

        // Query hanya untuk screening yang:
        // 1. Belum ditangani oleh psikolog
        // 2. Sudah ditangani oleh relawan
        // 3. Memiliki skor tinggi atau medium (perlu psikolog)
        return parent::getEloquentQuery()
            ->whereNotIn('id', $handledByPsychologistIds)
            ->whereIn('id', $handledByVolunteerIds)
            // ->where('score', '>=', 60) // Hanya skor medium ke atas yang perlu psikolog
            ->with(['user', 'volunteerResponses.volunteer']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Nama Responden')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('score')
                    ->label('Skor')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        $state >= 80 => 'danger',
                        $state >= 60 => 'warning',
                        default => 'success',
                    }),
                TextColumn::make('result')
                    ->label('Hasil Screening')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Risiko Tinggi' => 'danger',
                        'Risiko Sedang' => 'warning',
                        'Risiko Rendah' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('screening_date')
                    ->label('Tanggal Screening')
                    ->date()
                    ->sortable(),
                TextColumn::make('volunteer_status')
                    ->label('Status Relawan')
                    ->getStateUsing(function ($record) {
                        $volunteerResponse = $record->volunteerResponses->first();
                        if ($volunteerResponse) {
                            return 'Sudah ditangani oleh ' . $volunteerResponse->volunteer->name;
                        }
                        return 'Belum ditangani relawan';
                    })
                    ->badge()
                    ->color(function ($record) {
                        return $record->volunteerResponses->isNotEmpty() ? 'success' : 'warning';
                    })
                    ->searchable(false)
                    ->sortable(false),
                TextColumn::make('volunteer_attachment_display')
                    ->label('Lampiran Relawan')
                    ->getStateUsing(function ($record) {
                        $volunteerResponse = $record->volunteerResponses->first();
                        if ($volunteerResponse && $volunteerResponse->attachment) {
                            $filename = basename($volunteerResponse->attachment);
                            $url = route('download.volunteer-attachment', ['filename' => $filename]);
                            return "<a href='{$url}' target='_blank' class='inline-flex items-center px-3 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-wide hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 transition ease-in-out duration-150'><svg class='w-3 h-3 mr-1' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 10v6m0 0l-3-3m3 3l3-3'></path></svg>Download</a>";
                        }
                        return '<span class="text-gray-400 text-xs">Tidak ada</span>';
                    })
                    ->html()
                    ->searchable(false)
                    ->sortable(false),
                TextColumn::make('topoplot_preview')
                    ->label('Preview Topoplot')
                    ->getStateUsing(function ($record) {
                        $volunteerResponse = $record->volunteerResponses->first();
                        if ($volunteerResponse && $volunteerResponse->attachment) {
                            $fileExtension = strtolower(pathinfo($volunteerResponse->attachment, PATHINFO_EXTENSION));
                            if ($fileExtension === 'csv') {
                                $filePath = storage_path('app/public/' . $volunteerResponse->attachment);

                                // Cek apakah topoplot sudah ada di cache
                                $cacheKey = 'topoplot_' . md5($volunteerResponse->attachment);
                                $cachedImagePath = storage_path('app/topoplot_cache/' . $cacheKey . '.png');

                                if (file_exists($cachedImagePath)) {
                                    $imageUrl = asset('storage/topoplot_cache/' . $cacheKey . '.png');
                                    return "<div class='text-center'><img src='{$imageUrl}' alt='Topoplot' class='max-w-24 max-h-24 object-contain rounded-lg border mx-auto' /></div>";
                                } else {
                                    return "<div class='text-center'><span class='text-blue-600 text-xs'>Klik 'Generate' untuk membuat topoplot</span></div>";
                                }
                            }
                        }
                        return '<span class="text-gray-400 text-xs">Tidak tersedia</span>';
                    })
                    ->html()
                    ->searchable(false)
                    ->sortable(false),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->since(),
            ])
            ->filters([
                SelectFilter::make('result')
                    ->label('Hasil Screening')
                    ->options([
                        'Risiko Tinggi' => 'Risiko Tinggi',
                        'Risiko Sedang' => 'Risiko Sedang',
                        'Risiko Rendah' => 'Risiko Rendah',
                    ]),
                SelectFilter::make('score_range')
                    ->label('Rentang Skor')
                    ->options([
                        'high' => 'Tinggi (≥80)',
                        'medium' => 'Sedang (60-79)',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when($data['value'], function (Builder $query, string $value): Builder {
                            switch ($value) {
                                case 'high':
                                    return $query->where('score', '>=', 80);
                                case 'medium':
                                    return $query->whereBetween('score', [60, 79]);
                                default:
                                    return $query;
                            }
                        });
                    }),
            ])
            ->actions([
                Action::make('generate_topoplot')
                    ->label('Generate Topoplot')
                    ->icon('heroicon-o-photo')
                    ->color('info')
                    ->action(function (WellBeingScreening $record): void {
                        $volunteerResponse = $record->volunteerResponses->first();
                        if ($volunteerResponse && $volunteerResponse->attachment) {
                            $fileExtension = strtolower(pathinfo($volunteerResponse->attachment, PATHINFO_EXTENSION));
                            if ($fileExtension === 'csv') {
                                $filePath = storage_path('app/public/' . $volunteerResponse->attachment);
                                $imageBase64 = TopoplotService::generateTopoplot($filePath, [], true); // Force regenerate

                                if ($imageBase64) {
                                    \Filament\Notifications\Notification::make()
                                        ->title('Topoplot berhasil di-generate!')
                                        ->success()
                                        ->send();
                                } else {
                                    \Filament\Notifications\Notification::make()
                                        ->title('Gagal generate topoplot')
                                        ->body('Pastikan API topoplot berjalan dan file CSV valid.')
                                        ->danger()
                                        ->send();
                                }
                            } else {
                                \Filament\Notifications\Notification::make()
                                    ->title('File bukan CSV')
                                    ->body('Topoplot hanya dapat di-generate dari file CSV.')
                                    ->warning()
                                    ->send();
                            }
                        }
                    })
                    ->visible(function (WellBeingScreening $record): bool {
                        $volunteerResponse = $record->volunteerResponses->first();
                        return $volunteerResponse &&
                               $volunteerResponse->attachment &&
                               strtolower(pathinfo($volunteerResponse->attachment, PATHINFO_EXTENSION)) === 'csv';
                    }),
                Action::make('assign_psychologist')
                    ->label('Beri Tanggapan')
                    ->color('success')
                    ->form([
                        \Filament\Forms\Components\Placeholder::make('volunteer_info')
                            ->label('Informasi Volunteer Response')
                            ->content(function ($record) {
                                $volunteerResponse = $record->volunteerResponses->first();
                                if ($volunteerResponse) {
                                    $content = "<div class='p-4 bg-green-50 border border-green-200 rounded-lg'>";
                                    $content .= "<h4 class='font-semibold text-green-800 mb-2'>Sudah ditangani oleh relawan</h4>";
                                    $content .= "<p><strong>Relawan:</strong> {$volunteerResponse->volunteer->name}</p>";
                                    $content .= "<p><strong>Tanggal:</strong> {$volunteerResponse->created_at->format('d/m/Y H:i')}</p>";
                                    $content .= "<p><strong>Catatan:</strong> {$volunteerResponse->notes}</p>";
                                    if ($volunteerResponse->attachment) {
                                        $filename = basename($volunteerResponse->attachment);
                                        $url = route('download.volunteer-attachment', ['filename' => $filename]);
                                        $content .= "<div class='mt-3'><a href='{$url}' target='_blank' class='inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150'>Download Lampiran Relawan</a></div>";
                                    }
                                    $content .= "</div>";
                                    return new HtmlString($content);
                                }
                                return new HtmlString("<div class='p-4 bg-yellow-50 border border-yellow-200 rounded-lg'><p class='text-yellow-800'>Belum ditangani oleh relawan</p></div>");
                            })
                            ->columnSpanFull(),
                        \Filament\Forms\Components\Placeholder::make('topoplot_preview')
                            ->label('Preview Topoplot EEG')
                            ->content(function ($record) {
                                $volunteerResponse = $record->volunteerResponses->first();
                                if ($volunteerResponse && $volunteerResponse->attachment) {
                                    $fileExtension = strtolower(pathinfo($volunteerResponse->attachment, PATHINFO_EXTENSION));
                                    if ($fileExtension === 'csv') {
                                        $filePath = storage_path('app/public/' . $volunteerResponse->attachment);
                                        if (file_exists($filePath)) {
                                            $imageBase64 = TopoplotService::generateTopoplot($filePath);
                                            if ($imageBase64) {
                                                $content = "<div class='p-4 bg-blue-50 border border-blue-200 rounded-lg'>";
                                                $content .= "<div class='flex justify-between items-center mb-3'>";
                                                $content .= "<h4 class='font-semibold text-blue-800'>Visualisasi Data EEG</h4>";
                                                $content .= "</div>";
                                                $content .= "<div class='flex justify-center'>";
                                                $content .= "<img src='data:image/png;base64,{$imageBase64}' alt='Topoplot EEG' class='max-w-md max-h-96 object-contain rounded-lg border-2 border-blue-300 shadow-lg' />";
                                                $content .= "</div>";
                                                $content .= "<p class='text-sm text-blue-600 mt-2 text-center'>Topoplot berdasarkan data EEG dari relawan</p>";
                                                $content .= "</div>";
                                                return new HtmlString($content);
                                            } else {
                                                $content = "<div class='p-4 bg-yellow-50 border border-yellow-200 rounded-lg'>";
                                                $content .= "<h4 class='font-semibold text-yellow-800 mb-2'>Topoplot Belum Tersedia</h4>";
                                                $content .= "<p class='text-yellow-600 text-center mb-3'>Topoplot belum di-generate. Silakan generate terlebih dahulu untuk melihat visualisasi EEG sebagai referensi screening.</p>";
                                                $content .= "<div class='text-center'>";
                                                $content .= "<span class='text-xs text-gray-600'>Gunakan tombol 'Generate Topoplot' di table list atau refresh halaman setelah generate</span>";
                                                $content .= "</div>";
                                                $content .= "</div>";
                                                return new HtmlString($content);
                                            }
                                        }
                                    }
                                }
                                return new HtmlString("<div class='p-4 bg-gray-50 border border-gray-200 rounded-lg'><p class='text-gray-600 text-center'>File CSV tidak tersedia untuk generate topoplot</p></div>");
                            })
                            ->columnSpanFull(),
                        Textarea::make('diagnosis')
                            ->label('Catatan')
                            ->rows(4)
                            ->placeholder('Masukkan catatan berdasarkan hasil screening dan topoplot...')
                            ->required(),
                        Textarea::make('recommendation')
                            ->label('Rekomendasi')
                            ->rows(4)
                            ->placeholder('Masukkan rekomendasi penanganan untuk klien...')
                            ->required(),
                        FileUpload::make('attachment')
                            ->label('Lampiran File')
                            ->acceptedFileTypes(['text/csv', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/pdf'])
                            ->maxSize(10240) // 10MB
                            ->helperText('Upload file CSV, Excel, atau PDF. Maksimal ukuran: 10MB')
                            ->directory('psychologist-attachments')
                            ->disk('public')
                            ->visibility('private')
                            ->previewable(false)
                            ->nullable(),
                    ])
                    ->action(function (WellBeingScreening $record, array $data): void {
                        PsychologistResponse::create([
                            'screening_id' => $record->id,
                            'psychologist_id' => Auth::id(), // Menggunakan psikolog yang sedang login
                            'diagnosis' => $data['diagnosis'],
                            'recommendation' => $data['recommendation'],
                            'attachment' => $data['attachment'] ?? null,
                        ]);

                        // Notification atau redirect bisa ditambahkan di sini
                    })
                    ->successNotificationTitle('Screening berhasil ditanggapi oleh psikolog')
                    ->requiresConfirmation()
                    ->modalHeading('Tanggapi Screening')
                    ->modalDescription('Berikan diagnosis dan rekomendasi untuk menangani screening ini')
                    ->slideOver()
                    ->modalWidth('4xl'),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Tidak ada screening yang perlu ditangani psikolog')
            ->emptyStateDescription('Semua screening sudah ditangani oleh psikolog atau skornya tidak memerlukan penanganan psikolog')
            ->emptyStateIcon('heroicon-o-check-circle');
    }

    public static function canCreate(): bool
    {
        return false; // Tidak bisa create karena ini read-only untuk screening yang belum ditangani
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\UnhandledPsychologistScreeningResource\Pages\ListUnhandledPsychologistScreenings::route('/'),
        ];
    }
}
