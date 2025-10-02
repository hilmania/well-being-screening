<?php

namespace App\Filament\Resources\Settings\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Fieldset;

class SettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('key')
                    ->label('Setting Key')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->placeholder('e.g., chatbot_url'),

                TextInput::make('label')
                    ->label('Label')
                    ->required()
                    ->placeholder('e.g., Chatbot URL'),

                Select::make('type')
                    ->label('Type')
                    ->options([
                        'text' => 'Text',
                        'url' => 'URL',
                        'boolean' => 'Boolean',
                        'number' => 'Number',
                        'textarea' => 'Textarea',
                    ])
                    ->required()
                    ->default('text')
                    ->live(),

                Select::make('group')
                    ->label('Group')
                    ->options([
                        'general' => 'General',
                        'chatbot' => 'Chatbot',
                        'email' => 'Email',
                        'sms' => 'SMS',
                        'notification' => 'Notification',
                    ])
                    ->required()
                    ->default('general'),

                // Text input for text, url, number
                TextInput::make('value')
                    ->label('Value')
                    ->visible(fn ($get) => in_array($get('type'), ['text', 'url', 'number']))
                    ->required(fn ($get) => in_array($get('type'), ['text', 'url', 'number']))
                    ->placeholder(fn ($get) => match($get('type')) {
                        'url' => 'https://example.com',
                        'number' => '0',
                        default => 'Enter the setting value'
                    })
                    ->rules(fn ($get) => match($get('type')) {
                        'url' => ['required', 'url'],
                        'number' => ['required', 'numeric'],
                        default => ['required', 'string']
                    }),

                // Textarea for long text
                Textarea::make('value')
                    ->label('Value')
                    ->visible(fn ($get) => $get('type') === 'textarea')
                    ->required(fn ($get) => $get('type') === 'textarea')
                    ->placeholder('Enter the setting value')
                    ->rows(4),

                // Toggle for boolean with custom field name
                Toggle::make('is_enabled')
                    ->label('Enabled')
                    ->visible(fn ($get) => $get('type') === 'boolean')
                    ->dehydrated(false), // Don't save this field to database

                Textarea::make('description')
                    ->label('Description')
                    ->placeholder('Brief description of this setting')
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }
}
