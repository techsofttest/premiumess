<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Brand;
use Illuminate\Auth\Access\HandlesAuthorization;

class BrandPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('categories.view');
    }

    public function view(User $user, Brand $brand): bool
    {
        return $user->can('categories.view');
    }

    public function create(User $user): bool
    {
        return $user->can('categories.create');
    }

    public function update(User $user, Brand $brand): bool
    {
        return $user->can('categories.update');
    }

    public function delete(User $user, Brand $brand): bool
    {
        return $user->can('categories.delete');
    }
}
