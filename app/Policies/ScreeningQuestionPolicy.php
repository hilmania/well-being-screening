<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ScreeningQuestion;
use Illuminate\Auth\Access\HandlesAuthorization;

class ScreeningQuestionPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ScreeningQuestion');
    }

    public function view(AuthUser $authUser, ScreeningQuestion $screeningQuestion): bool
    {
        return $authUser->can('View:ScreeningQuestion');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ScreeningQuestion');
    }

    public function update(AuthUser $authUser, ScreeningQuestion $screeningQuestion): bool
    {
        return $authUser->can('Update:ScreeningQuestion');
    }

    public function delete(AuthUser $authUser, ScreeningQuestion $screeningQuestion): bool
    {
        return $authUser->can('Delete:ScreeningQuestion');
    }

    public function restore(AuthUser $authUser, ScreeningQuestion $screeningQuestion): bool
    {
        return $authUser->can('Restore:ScreeningQuestion');
    }

    public function forceDelete(AuthUser $authUser, ScreeningQuestion $screeningQuestion): bool
    {
        return $authUser->can('ForceDelete:ScreeningQuestion');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ScreeningQuestion');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ScreeningQuestion');
    }

    public function replicate(AuthUser $authUser, ScreeningQuestion $screeningQuestion): bool
    {
        return $authUser->can('Replicate:ScreeningQuestion');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ScreeningQuestion');
    }

}