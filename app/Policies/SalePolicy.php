<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Sale;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SalePolicy
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
        return $user->can('ViewAny:Sale');
    }

    public function view(User $user, Sale $sale): bool
    {
        return $user->can('View:Sale');
    }

    public function create(User $user): bool
    {
        return $user->can('Create:Sale');
    }

    public function update(User $user, Sale $sale): bool
    {
        return $user->can('Update:Sale');
    }

    public function delete(User $user, Sale $sale): bool
    {
        return $user->can('Delete:Sale');
    }

    public function restore(User $user, Sale $sale): bool
    {
        return $user->can('Restore:Sale');
    }

    public function forceDelete(User $user, Sale $sale): bool
    {
        return $user->can('ForceDelete:Sale');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can('ForceDeleteAny:Sale');
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('RestoreAny:Sale');
    }

    public function replicate(User $user, Sale $sale): bool
    {
        return $user->can('Replicate:Sale');
    }

    public function reorder(User $user): bool
    {
        return $user->can('Reorder:Sale');
    }
}
