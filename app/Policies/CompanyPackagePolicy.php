<?php

namespace App\Policies;

use App\Models\CompanyPackage;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CompanyPackagePolicy
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
    public function view(User $user, CompanyPackage $companyPackage): bool
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
    public function update(User $user, CompanyPackage $companyPackage): bool
    {
        return $user->role === 'superadmin';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CompanyPackage $companyPackage): bool
    {
        return $user->role === 'superadmin';
    }

    /**
     * Determine whether the user can assign packages.
     */
    public function assign(User $user): bool
    {
        return $user->role === 'superadmin';
    }

    /**
     * Determine whether the user can reassign packages.
     */
    public function reassign(User $user, CompanyPackage $companyPackage): bool
    {
        return $user->role === 'superadmin';
    }
}