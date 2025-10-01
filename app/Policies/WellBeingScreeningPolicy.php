<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\WellBeingScreening;
use Illuminate\Auth\Access\HandlesAuthorization;

class WellBeingScreeningPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:WellBeingScreening');
    }

    public function view(AuthUser $authUser, WellBeingScreening $wellBeingScreening): bool
    {
        return $authUser->can('View:WellBeingScreening');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:WellBeingScreening');
    }

    public function update(AuthUser $authUser, WellBeingScreening $wellBeingScreening): bool
    {
        return $authUser->can('Update:WellBeingScreening');
    }

    public function delete(AuthUser $authUser, WellBeingScreening $wellBeingScreening): bool
    {
        return $authUser->can('Delete:WellBeingScreening');
    }

    public function restore(AuthUser $authUser, WellBeingScreening $wellBeingScreening): bool
    {
        return $authUser->can('Restore:WellBeingScreening');
    }

    public function forceDelete(AuthUser $authUser, WellBeingScreening $wellBeingScreening): bool
    {
        return $authUser->can('ForceDelete:WellBeingScreening');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:WellBeingScreening');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:WellBeingScreening');
    }

    public function replicate(AuthUser $authUser, WellBeingScreening $wellBeingScreening): bool
    {
        return $authUser->can('Replicate:WellBeingScreening');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:WellBeingScreening');
    }

}