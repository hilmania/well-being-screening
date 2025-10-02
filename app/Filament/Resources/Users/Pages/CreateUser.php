<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Hash password
        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $user = $this->record;
        $roleNames = $this->form->getState()['roles'] ?? [];

        // Assign roles to new user by name
        if (!empty($roleNames)) {
            $user->syncRoles($roleNames);
        }
    }
}
