<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Seo;
use Illuminate\Auth\Access\HandlesAuthorization;

class SeoPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('settings.manage');
    }

    public function view(User $user, Seo $seo): bool
    {
        return $user->can('settings.manage');
    }

    public function create(User $user): bool
    {
        return $user->can('settings.manage');
    }

    public function update(User $user, Seo $seo): bool
    {
        return $user->can('settings.manage');
    }

    public function delete(User $user, Seo $seo): bool
    {
        return $user->can('settings.manage');
    }
}
