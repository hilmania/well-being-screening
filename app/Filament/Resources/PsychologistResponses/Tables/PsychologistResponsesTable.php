<?php

namespace App\Filament\Resources\PsychologistResponses\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PsychologistResponsesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('screening.user.name')
                    ->label('Nama Responden')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('screening.score')
                    ->label('Skor Screening')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        $state >= 80 => 'danger',
                        $state >= 60 => 'warning',
                        default => 'success',
                    }),
                TextColumn::make('screening.screening_date')
                    ->label('Tanggal Screening')
                    ->date()
                    ->sortable(),
                TextColumn::make('psychologist.name')
                    ->label('Psikolog')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('diagnosis')
                    ->label('Diagnosis')
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }
                        return $state;
                    }),
                TextColumn::make('recommendation')
                    ->label('Rekomendasi')
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }
                        return $state;
                    }),
                TextColumn::make('attachment')
                    ->label('Lampiran')
                    ->formatStateUsing(function ($state) {
                        if ($state) {
                            $filename = basename($state);
                            // Menggunakan asset URL langsung ke storage public
                            $url = asset('storage/' . $state);
                            return "<a href='{$url}' download='{$filename}' class='inline-flex items-center px-3 py-1 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors'><svg class='w-4 h-4 mr-2' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'></path></svg>Download</a>";
                        }
                        return '<span class="text-gray-400">Tidak ada</span>';
                    })
                    ->html(),
                TextColumn::make('created_at')
                    ->label('Tanggal Respon')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('psychologist_id')
                    ->label('Psikolog')
                    ->relationship('psychologist', 'name')
                    ->searchable(),
                \Filament\Tables\Filters\SelectFilter::make('score_range')
                    ->label('Rentang Skor')
                    ->options([
                        'high' => 'Tinggi (≥80)',
                        'medium' => 'Sedang (60-79)',
                    ])
                    ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data): \Illuminate\Database\Eloquent\Builder {
                        return $query->when($data['value'], function (\Illuminate\Database\Eloquent\Builder $query, string $value): \Illuminate\Database\Eloquent\Builder {
                            switch ($value) {
                                case 'high':
                                    return $query->whereHas('screening', fn($q) => $q->where('score', '>=', 80));
                                case 'medium':
                                    return $query->whereHas('screening', fn($q) => $q->whereBetween('score', [60, 79]));
                                default:
                                    return $query;
                            }
                        });
                    }),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
