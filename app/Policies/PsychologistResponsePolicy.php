<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\PsychologistResponse;
use Illuminate\Auth\Access\HandlesAuthorization;

class PsychologistResponsePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PsychologistResponse');
    }

    public function view(AuthUser $authUser, PsychologistResponse $psychologistResponse): bool
    {
        return $authUser->can('View:PsychologistResponse');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PsychologistResponse');
    }

    public function update(AuthUser $authUser, PsychologistResponse $psychologistResponse): bool
    {
        return $authUser->can('Update:PsychologistResponse');
    }

    public function delete(AuthUser $authUser, PsychologistResponse $psychologistResponse): bool
    {
        return $authUser->can('Delete:PsychologistResponse');
    }

    public function restore(AuthUser $authUser, PsychologistResponse $psychologistResponse): bool
    {
        return $authUser->can('Restore:PsychologistResponse');
    }

    public function forceDelete(AuthUser $authUser, PsychologistResponse $psychologistResponse): bool
    {
        return $authUser->can('ForceDelete:PsychologistResponse');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PsychologistResponse');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PsychologistResponse');
    }

    public function replicate(AuthUser $authUser, PsychologistResponse $psychologistResponse): bool
    {
        return $authUser->can('Replicate:PsychologistResponse');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PsychologistResponse');
    }

}