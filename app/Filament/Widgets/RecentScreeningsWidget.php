<?php

namespace App\Filament\Widgets;

use App\Models\WellBeingScreening;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentScreeningsWidget extends TableWidget
{
    protected static ?string $heading = 'Screening Terbaru';
    
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                WellBeingScreening::query()
                    ->with(['user', 'volunteerResponses', 'psychologistResponses'])
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('user.name')
                    ->label('Responden')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('screening_date')
                    ->label('Tanggal Screening')
                    ->date()
                    ->sortable(),
                    
                TextColumn::make('score')
                    ->label('Skor')
                    ->numeric()
                    ->sortable(),
                    
                TextColumn::make('volunteerResponses')
                    ->label('Status Relawan')
                    ->badge()
                    ->getStateUsing(fn ($record) => $record->volunteerResponses->count() > 0 ? 'Sudah Ditangani' : 'Belum Ditangani')
                    ->color(fn (string $state): string => match ($state) {
                        'Sudah Ditangani' => 'success',
                        'Belum Ditangani' => 'warning',
                    }),
                    
                TextColumn::make('psychologistResponses')
                    ->label('Status Psikolog')
                    ->badge()
                    ->getStateUsing(fn ($record) => $record->psychologistResponses->count() > 0 ? 'Sudah Ditangani' : 'Belum Ditangani')
                    ->color(fn (string $state): string => match ($state) {
                        'Sudah Ditangani' => 'success',
                        'Belum Ditangani' => 'danger',
                    }),
                    
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->since(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
