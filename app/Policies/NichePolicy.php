<?php

namespace App\Policies;

use App\Models\Niche;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class NichePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isHelper() || $user->isAuditor();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Niche $niche): bool
    {
        return $user->isAdmin() || $user->isHelper() || $user->isAuditor();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isHelper();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Niche $niche): bool
    {
        return $user->isAdmin() || $user->isHelper();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Niche $niche): bool
    {
        return $user->isAdmin() || $user->isHelper();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Niche $niche): bool
    {
        return $user->isAdmin() || $user->isHelper();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Niche $niche): bool
    {
        return $user->isAdmin() || $user->isHelper();
    }
}
