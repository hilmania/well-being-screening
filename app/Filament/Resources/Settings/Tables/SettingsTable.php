<?php

namespace App\Filament\Resources\Settings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;

class SettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('label')
                    ->label('Setting')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('key')
                    ->label('Key')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                BadgeColumn::make('group')
                    ->label('Group')
                    ->colors([
                        'primary' => 'general',
                        'success' => 'chatbot',
                        'warning' => 'email',
                        'danger' => 'notification',
                    ]),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'text' => 'gray',
                        'url' => 'blue',
                        'boolean' => 'green',
                        'number' => 'orange',
                        'textarea' => 'purple',
                    }),

                TextColumn::make('value')
                    ->label('Current Value')
                    ->limit(50)
                    ->wrap()
                    ->formatStateUsing(fn ($state, $record) =>
                        $record->type === 'boolean'
                            ? ($state === '1' ? 'Enabled' : 'Disabled')
                            : $state
                    )
                    ->badge(fn ($record) => $record->type === 'boolean')
                    ->color(fn ($state, $record) =>
                        $record->type === 'boolean'
                            ? ($state === '1' ? 'success' : 'danger')
                            : 'gray'
                    ),

                TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('group')
                    ->options([
                        'general' => 'General',
                        'chatbot' => 'Chatbot',
                        'email' => 'Email',
                        'sms' => 'SMS',
                        'notification' => 'Notification',
                    ]),

                SelectFilter::make('type')
                    ->options([
                        'text' => 'Text',
                        'url' => 'URL',
                        'boolean' => 'Boolean',
                        'number' => 'Number',
                        'textarea' => 'Textarea',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('group');
    }
}
