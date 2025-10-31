<?php

namespace App\Policies;

use App\Models\CompanyPackageInvoice;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class InvoicePolicy
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
    public function view(User $user, CompanyPackageInvoice $invoice): bool
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
    public function update(User $user, CompanyPackageInvoice $invoice): bool
    {
        return $user->role === 'superadmin';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CompanyPackageInvoice $invoice): bool
    {
        return $user->role === 'superadmin';
    }

    /**
     * Determine whether the user can mark invoice as paid.
     */
    public function markPaid(User $user, CompanyPackageInvoice $invoice): bool
    {
        return $user->role === 'superadmin';
    }

    /**
     * Determine whether the user can send invoice.
     */
    public function sendInvoice(User $user, CompanyPackageInvoice $invoice): bool
    {
        return $user->role === 'superadmin';
    }
}