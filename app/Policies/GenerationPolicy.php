<?php

namespace App\Policies;

use App\Models\Generation;
use App\Models\User;

/**
 * Admins operate the service and only see reports, so they cannot generate images.
 */
class GenerationPolicy
{
    public function viewAny(User $user): bool
    {
        return ! $user->isAdmin();
    }

    public function view(User $user, Generation $generation): bool
    {
        if ($user->isAdmin()) {
            return false;
        }

        return $generation->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return ! $user->isAdmin();
    }
}
