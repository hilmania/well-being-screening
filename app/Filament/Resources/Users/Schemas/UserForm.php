<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Spatie\Permission\Models\Role;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                Select::make('roles')
                    ->label('Role')
                    ->multiple()
                    ->options(function () {
                        return Role::all()->pluck('name', 'name');
                    })
                    ->default(function ($record) {
                        return $record ? $record->roles->pluck('name')->toArray() : [];
                    })
                    ->searchable()
                    ->preload()
                    ->helperText('Pilih role untuk user ini')
                    ->placeholder('Pilih role...')
                    ->dehydrated(false), // Prevent auto-save, handled manually
                DateTimePicker::make('email_verified_at'),
                TextInput::make('password')
                    ->password()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->revealable(),
            ]);
    }
}
