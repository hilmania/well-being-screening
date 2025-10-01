<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ScreeningAnswer;
use Illuminate\Auth\Access\HandlesAuthorization;

class ScreeningAnswerPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ScreeningAnswer');
    }

    public function view(AuthUser $authUser, ScreeningAnswer $screeningAnswer): bool
    {
        return $authUser->can('View:ScreeningAnswer');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ScreeningAnswer');
    }

    public function update(AuthUser $authUser, ScreeningAnswer $screeningAnswer): bool
    {
        return $authUser->can('Update:ScreeningAnswer');
    }

    public function delete(AuthUser $authUser, ScreeningAnswer $screeningAnswer): bool
    {
        return $authUser->can('Delete:ScreeningAnswer');
    }

    public function restore(AuthUser $authUser, ScreeningAnswer $screeningAnswer): bool
    {
        return $authUser->can('Restore:ScreeningAnswer');
    }

    public function forceDelete(AuthUser $authUser, ScreeningAnswer $screeningAnswer): bool
    {
        return $authUser->can('ForceDelete:ScreeningAnswer');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ScreeningAnswer');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ScreeningAnswer');
    }

    public function replicate(AuthUser $authUser, ScreeningAnswer $screeningAnswer): bool
    {
        return $authUser->can('Replicate:ScreeningAnswer');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ScreeningAnswer');
    }

}