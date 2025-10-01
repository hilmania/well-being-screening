<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\VolunteersResponse;
use Illuminate\Auth\Access\HandlesAuthorization;

class VolunteersResponsePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:VolunteersResponse');
    }

    public function view(AuthUser $authUser, VolunteersResponse $volunteersResponse): bool
    {
        return $authUser->can('View:VolunteersResponse');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:VolunteersResponse');
    }

    public function update(AuthUser $authUser, VolunteersResponse $volunteersResponse): bool
    {
        return $authUser->can('Update:VolunteersResponse');
    }

    public function delete(AuthUser $authUser, VolunteersResponse $volunteersResponse): bool
    {
        return $authUser->can('Delete:VolunteersResponse');
    }

    public function restore(AuthUser $authUser, VolunteersResponse $volunteersResponse): bool
    {
        return $authUser->can('Restore:VolunteersResponse');
    }

    public function forceDelete(AuthUser $authUser, VolunteersResponse $volunteersResponse): bool
    {
        return $authUser->can('ForceDelete:VolunteersResponse');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:VolunteersResponse');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:VolunteersResponse');
    }

    public function replicate(AuthUser $authUser, VolunteersResponse $volunteersResponse): bool
    {
        return $authUser->can('Replicate:VolunteersResponse');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:VolunteersResponse');
    }

}