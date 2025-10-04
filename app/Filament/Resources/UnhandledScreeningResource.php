<?php

namespace App\Filament\Resources;

use App\Models\WellBeingScreening;
use App\Models\VolunteersResponse;
use App\Models\User;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\Auth;
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
                    ->label('Nama Responden')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.gender')
                    ->label('Jenis Kelamin')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'male' => 'Laki-laki',
                        'female' => 'Perempuan',
                        default => '-',
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'male' => 'blue',
                        'female' => 'pink',
                        default => 'gray',
                    }),
                TextColumn::make('user.birth_date')
                    ->label('Tanggal Lahir')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('user.phone')
                    ->label('Nomor Telepon')
                    ->searchable(),
                TextColumn::make('user.address')
                    ->label('Alamat')
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 30) {
                            return null;
                        }
                        return $state;
                    }),
                TextColumn::make('screening_date')
                    ->label('Tanggal Screening')
                    ->date('d/m/Y')
                    ->sortable(),
                // TextColumn::make('score')
                //     ->label('Skor')
                //     ->numeric()
                //     ->sortable()
                //     ->badge()
                //     ->color(fn (string $state): string => match (true) {
                //         $state >= 80 => 'danger',
                //         $state >= 60 => 'warning',
                //         default => 'success',
                //     }),
                // TextColumn::make('result')
                //     ->label('Hasil')
                //     ->badge()
                //     ->color(fn (string $state): string => match ($state) {
                //         'Risiko Tinggi' => 'danger',
                //         'Risiko Sedang' => 'warning',
                //         'Risiko Rendah' => 'success',
                //         default => 'gray',
                //     }),
            ])
            ->filters([
                SelectFilter::make('result')
                    ->label('Hasil Screening')
                    ->options([
                        'Risiko Tinggi' => 'Risiko Tinggi',
                        'Risiko Sedang' => 'Risiko Sedang',
                        'Risiko Rendah' => 'Risiko Rendah',
                    ]),
                // SelectFilter::make('gender')
                //     ->label('Jenis Kelamin')
                //     ->relationship('user', 'gender')
                //     ->options([
                //         'male' => 'Laki-laki',
                //         'female' => 'Perempuan',
                //     ]),
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
                Action::make('view_answers')
                    ->label('Lihat Jawaban')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->modalHeading('Jawaban Responden')
                    ->modalContent(function (WellBeingScreening $record): \Illuminate\Contracts\View\View {
                        $answers = $record->answers()->with('question')->get();
                        return view('filament.components.screening-answers', [
                            'answers' => $answers,
                            'screening' => $record,
                        ]);
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup'),
                Action::make('assign_volunteer')
                    ->label('Tanggapi')
                    ->icon('heroicon-o-user-plus')
                    ->color('success')
                    ->form([
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
                            ->disk('public')
                            ->visibility('private')
                            ->previewable(false)
                            ->nullable(),
                    ])
                    ->action(function (WellBeingScreening $record, array $data): void {
                        VolunteersResponse::create([
                            'screening_id' => $record->id,
                            'volunteer_id' => Auth::id(), // Menggunakan relawan yang sedang login
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
