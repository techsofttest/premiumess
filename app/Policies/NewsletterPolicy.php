<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Newsletter;
use Illuminate\Auth\Access\HandlesAuthorization;

class NewsletterPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('settings.manage');
    }

    public function view(User $user, Newsletter $newsletter): bool
    {
        return $user->can('settings.manage');
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Newsletter $newsletter): bool
    {
        return false;
    }

    public function delete(User $user, Newsletter $newsletter): bool
    {
        return $user->can('settings.manage');
    }
}
