<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Advertisement;
use Illuminate\Auth\Access\HandlesAuthorization;

class AdvertisementPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('advertisements.view');
    }

    public function view(User $user, Advertisement $advertisement): bool
    {
        return $user->can('advertisements.view');
    }

    public function create(User $user): bool
    {
        return $user->can('settings.manage');
    }

    public function update(User $user, Advertisement $advertisement): bool
    {
        return $user->can('advertisements.update');
    }

    public function delete(User $user, Advertisement $advertisement): bool
    {
        return $user->can('settings.manage');
    }
}
