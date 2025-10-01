<?php

namespace App\Filament\Resources\VolunteersResponses\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use App\Models\WellBeingScreening;
use App\Models\VolunteersResponse;

class VolunteersResponseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('screening_id')
                    ->label('Screening yang Belum Ditangani')
                    ->options(function () {
                        $handledScreeningIds = VolunteersResponse::pluck('screening_id')->toArray();
                        return WellBeingScreening::whereNotIn('id', $handledScreeningIds)
                            ->with('user')
                            ->get()
                            ->mapWithKeys(function ($screening) {
                                $userInfo = $screening->user ? $screening->user->name : 'No User';
                                $scoreInfo = "Skor: {$screening->score}";
                                $dateInfo = $screening->screening_date ? $screening->screening_date->format('d/m/Y') : 'No Date';

                                return [
                                    $screening->id => "#{$screening->id} - {$userInfo} - {$scoreInfo} - {$dateInfo}"
                                ];
                            });
                    })
                    ->searchable()
                    ->required()
                    ->helperText('Pilih screening yang belum ditangani oleh relawan')
                    ->placeholder('Pilih screening...'),
                Select::make('volunteer_id')
                    ->label('Relawan')
                    ->relationship('volunteer', 'name')
                    ->searchable()
                    ->required()
                    ->placeholder('Pilih relawan...')
                    ->createOptionForm([
                        \Filament\Forms\Components\TextInput::make('name')
                            ->label('Nama')
                            ->required(),
                        \Filament\Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required(),
                    ]),
                Textarea::make('notes')
                    ->label('Catatan Relawan')
                    ->rows(4)
                    ->placeholder('Masukkan catatan, saran, atau rekomendasi untuk klien...')
                    ->helperText('Berikan catatan yang membantu untuk follow-up dengan klien')
                    ->columnSpanFull(),
            ]);
    }
}
