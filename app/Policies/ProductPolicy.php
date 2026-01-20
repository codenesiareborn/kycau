<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductPolicy
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
        return $user->can('ViewAny:Product');
    }

    public function view(User $user, Product $product): bool
    {
        return $user->can('View:Product');
    }

    public function create(User $user): bool
    {
        return $user->can('Create:Product');
    }

    public function update(User $user, Product $product): bool
    {
        return $user->can('Update:Product');
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->can('Delete:Product');
    }

    public function restore(User $user, Product $product): bool
    {
        return $user->can('Restore:Product');
    }

    public function forceDelete(User $user, Product $product): bool
    {
        return $user->can('ForceDelete:Product');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can('ForceDeleteAny:Product');
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('RestoreAny:Product');
    }

    public function replicate(User $user, Product $product): bool
    {
        return $user->can('Replicate:Product');
    }

    public function reorder(User $user): bool
    {
        return $user->can('Reorder:Product');
    }
}
