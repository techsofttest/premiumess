<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Cms;
use Illuminate\Auth\Access\HandlesAuthorization;

class CmsPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('cms.view');
    }

    public function view(User $user, Cms $cms): bool
    {
        return $user->can('cms.view');
    }

    public function create(User $user): bool
    {
        return $user->can('settings.manage');
    }

    public function update(User $user, Cms $cms): bool
    {
        return $user->can('cms.update');
    }

    public function delete(User $user, Cms $cms): bool
    {
        return $user->can('settings.manage');
    }
}
