<?php

namespace App\Filament\Resources\VolunteersResponses\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use App\Models\WellBeingScreening;

class VolunteersResponsesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('screening.user.name')
                    ->label('Nama Klien')
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
                TextColumn::make('volunteer.name')
                    ->label('Relawan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('notes')
                    ->label('Catatan')
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }
                        return $state;
                    }),
                TextColumn::make('created_at')
                    ->label('Tanggal Respon')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('volunteer_id')
                    ->label('Relawan')
                    ->relationship('volunteer', 'name')
                    ->searchable(),
                SelectFilter::make('screening_score')
                    ->label('Kategori Skor')
                    ->options([
                        'high' => 'Tinggi (≥80)',
                        'medium' => 'Sedang (60-79)',
                        'low' => 'Rendah (<60)',
                    ])
                    ->query(function ($query, $data) {
                        return $query->when($data['value'], function ($query, $value) {
                            switch ($value) {
                                case 'high':
                                    return $query->whereHas('screening', fn($q) => $q->where('score', '>=', 80));
                                case 'medium':
                                    return $query->whereHas('screening', fn($q) => $q->whereBetween('score', [60, 79]));
                                case 'low':
                                    return $query->whereHas('screening', fn($q) => $q->where('score', '<', 60));
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
