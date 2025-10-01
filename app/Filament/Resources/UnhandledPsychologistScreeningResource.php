<?php

namespace App\Filament\Resources;

use App\Models\WellBeingScreening;
use App\Models\PsychologistResponse;
use App\Models\VolunteersResponse;
use App\Models\User;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Placeholder;
use Illuminate\Support\Facades\Auth;
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

    protected static ?string $navigationLabel = 'Screening Belum Ditangani Psikolog';

    protected static ?string $modelLabel = 'Screening Belum Ditangani Psikolog';

    protected static ?string $pluralModelLabel = 'Screening Belum Ditangani Psikolog';

    protected static string | UnitEnum | null $navigationGroup = 'Psychologist Management';

    public static function getEloquentQuery(): Builder
    {
        // Mendapatkan ID screening yang sudah ditangani oleh psikolog
        $handledScreeningIds = PsychologistResponse::pluck('screening_id')->toArray();

        // Query hanya untuk screening yang belum ditangani dan memiliki skor tinggi atau medium (perlu psikolog)
        return parent::getEloquentQuery()
            ->whereNotIn('id', $handledScreeningIds)
            ->where('score', '>=', 60) // Hanya skor medium ke atas yang perlu psikolog
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
                            $url = asset('storage/' . $volunteerResponse->attachment);
                            return "<a href='{$url}' download='{$filename}' class='inline-flex items-center px-3 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-wide hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 transition ease-in-out duration-150'><svg class='w-3 h-3 mr-1' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 10v6m0 0l-3-3m3 3l3-3'></path></svg>Download</a>";
                        }
                        return '<span class="text-gray-400 text-xs">Tidak ada</span>';
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
                                        $url = asset('storage/' . $volunteerResponse->attachment);
                                        $content .= "<div class='mt-3'><a href='{$url}' download='{$filename}' class='inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150'>Download Lampiran Relawan</a></div>";
                                    }
                                    $content .= "</div>";
                                    return new HtmlString($content);
                                }
                                return new HtmlString("<div class='p-4 bg-yellow-50 border border-yellow-200 rounded-lg'><p class='text-yellow-800'>Belum ditangani oleh relawan</p></div>");
                            })
                            ->columnSpanFull(),
                        Textarea::make('diagnosis')
                            ->label('Diagnosis')
                            ->rows(4)
                            ->placeholder('Masukkan diagnosis berdasarkan hasil screening...')
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
