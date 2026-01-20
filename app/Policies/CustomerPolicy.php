<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CustomerPolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasAnyRole(['super_admin', 'admin'])) {
            return null;
        }

        if (! $user->hasActivePackage()) {
            return false;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('ViewAny:Customer');
    }

    public function view(User $user, Customer $customer): bool
    {
        return $user->can('View:Customer');
    }

    public function create(User $user): bool
    {
        return $user->can('Create:Customer');
    }

    public function update(User $user, Customer $customer): bool
    {
        return $user->can('Update:Customer');
    }

    public function delete(User $user, Customer $customer): bool
    {
        return $user->can('Delete:Customer');
    }

    public function restore(User $user, Customer $customer): bool
    {
        return $user->can('Restore:Customer');
    }

    public function forceDelete(User $user, Customer $customer): bool
    {
        return $user->can('ForceDelete:Customer');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can('ForceDeleteAny:Customer');
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('RestoreAny:Customer');
    }

    public function replicate(User $user, Customer $customer): bool
    {
        return $user->can('Replicate:Customer');
    }

    public function reorder(User $user): bool
    {
        return $user->can('Reorder:Customer');
    }
}
