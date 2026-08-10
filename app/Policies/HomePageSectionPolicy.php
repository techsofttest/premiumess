<?php

namespace App\Policies;

use App\Models\User;
use App\Models\HomePageSection;
use Illuminate\Auth\Access\HandlesAuthorization;

class HomePageSectionPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('cms.view');
    }

    public function view(User $user, HomePageSection $section): bool
    {
        return $user->can('cms.view');
    }

    public function create(User $user): bool
    {
        return $user->can('cms.update');
    }

    public function update(User $user, HomePageSection $section): bool
    {
        return $user->can('cms.update');
    }

    public function delete(User $user, HomePageSection $section): bool
    {
        return $user->can('cms.update');
    }
}
