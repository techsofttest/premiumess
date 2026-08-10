<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Banner;
use Illuminate\Auth\Access\HandlesAuthorization;

class BannerPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('cms.view');
    }

    public function view(User $user, Banner $banner): bool
    {
        return $user->can('cms.view');
    }

    public function create(User $user): bool
    {
        return $user->can('cms.update');
    }

    public function update(User $user, Banner $banner): bool
    {
        return $user->can('cms.update');
    }

    public function delete(User $user, Banner $banner): bool
    {
        return $user->can('cms.update');
    }
}
