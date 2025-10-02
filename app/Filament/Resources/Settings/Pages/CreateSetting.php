<?php

namespace App\Filament\Resources\Settings\Pages;

use App\Filament\Resources\Settings\SettingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSetting extends CreateRecord
{
    protected static string $resource = SettingResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Handle boolean value sebelum create
        if ($data['type'] === 'boolean' && isset($data['is_enabled'])) {
            $data['value'] = $data['is_enabled'] ? '1' : '0';
            unset($data['is_enabled']);
        }

        return $data;
    }
}
