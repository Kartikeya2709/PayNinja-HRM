<?php

namespace App\Policies;

use App\Models\Package;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PackagePolicy
{
    use HandlesAuthorization;

    /**
     * Perform pre-authorization checks.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->role === 'superadmin') {
            return true;
        }
        return null;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->role === 'superadmin';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Package $package): bool
    {
        return $user->role === 'superadmin';
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role === 'superadmin';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Package $package): bool
    {
        return $user->role === 'superadmin';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Package $package): bool
    {
        return $user->role === 'superadmin';
    }

    /**
     * Determine whether the user can toggle active status.
     */
    public function toggleActive(User $user, Package $package): bool
    {
        return $user->role === 'superadmin';
    }
}