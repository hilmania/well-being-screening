<?php

namespace App\Filament\Resources;

use App\Models\WellBeingScreening;
use App\Models\VolunteersResponse;
use App\Models\User;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use BackedEnum;
use UnitEnum;

class UnhandledScreeningResource extends Resource
{
    protected static ?string $model = WellBeingScreening::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static ?string $navigationLabel = 'Screening Belum Ditangani';

    protected static ?string $modelLabel = 'Screening Belum Ditangani';

    protected static ?string $pluralModelLabel = 'Screening Belum Ditangani';

    protected static string|UnitEnum|null $navigationGroup = 'Responses';

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        // Hanya tampilkan screening yang belum ditangani oleh volunteer
        $handledScreeningIds = VolunteersResponse::pluck('screening_id')->toArray();

        return parent::getEloquentQuery()
            ->whereNotIn('id', $handledScreeningIds)
            ->with(['user', 'answers']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Nama Klien')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable(),
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
                    ->label('Hasil')
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
                        'low' => 'Rendah (<60)',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when($data['value'], function (Builder $query, string $value): Builder {
                            switch ($value) {
                                case 'high':
                                    return $query->where('score', '>=', 80);
                                case 'medium':
                                    return $query->whereBetween('score', [60, 79]);
                                case 'low':
                                    return $query->where('score', '<', 60);
                                default:
                                    return $query;
                            }
                        });
                    }),
            ])
            ->actions([
                Action::make('assign_volunteer')
                    ->label('Tanggapi')
                    ->icon('heroicon-o-user-plus')
                    ->color('success')
                    ->form([
                        Select::make('volunteer_id')
                            ->label('Pilih Relawan')
                            ->options(User::all()->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(4)
                            ->placeholder('Masukkan catatan, saran, atau rekomendasi untuk klien...')
                            ->required(),
                        FileUpload::make('attachment')
                            ->label('Lampiran File')
                            ->acceptedFileTypes(['text/csv', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                            ->maxSize(10240) // 10MB
                            ->helperText('Upload file CSV atau Excel. Maksimal ukuran: 10MB')
                            ->directory('volunteer-attachments')
                            ->nullable(),
                    ])
                    ->action(function (WellBeingScreening $record, array $data): void {
                        VolunteersResponse::create([
                            'screening_id' => $record->id,
                            'volunteer_id' => $data['volunteer_id'],
                            'notes' => $data['notes'],
                            'attachment' => $data['attachment'] ?? null,
                        ]);

                        // Notification atau redirect bisa ditambahkan di sini
                    })
                    ->successNotificationTitle('Screening berhasil ditanggapi oleh relawan')
                    ->requiresConfirmation()
                    ->modalHeading('Tanggapi Screening')
                    ->modalDescription('Pilih relawan dan berikan catatan untuk menangani screening ini'),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Tidak ada screening yang perlu ditangani')
            ->emptyStateDescription('Semua screening sudah ditangani oleh relawan')
            ->emptyStateIcon('heroicon-o-check-circle');
    }

    public static function canCreate(): bool
    {
        return false; // Tidak bisa create karena ini read-only untuk screening yang belum ditangani
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\UnhandledScreeningResource\Pages\ListUnhandledScreenings::route('/'),
        ];
    }
}
