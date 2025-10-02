<?php

namespace App\Filament\Resources\Settings\Pages;

use App\Filament\Resources\Settings\SettingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSetting extends EditRecord
{
    protected static string $resource = SettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Handle boolean value untuk edit form
        if ($data['type'] === 'boolean') {
            $data['is_enabled'] = $data['value'] === '1';
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Handle boolean value sebelum save
        if ($data['type'] === 'boolean' && isset($data['is_enabled'])) {
            $data['value'] = $data['is_enabled'] ? '1' : '0';
            unset($data['is_enabled']);
        }

        return $data;
    }
}
