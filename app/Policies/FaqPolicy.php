<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Faq;
use Illuminate\Auth\Access\HandlesAuthorization;

class FaqPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('cms.view');
    }

    public function view(User $user, Faq $faq): bool
    {
        return $user->can('cms.view');
    }

    public function create(User $user): bool
    {
        return $user->can('cms.update');
    }

    public function update(User $user, Faq $faq): bool
    {
        return $user->can('cms.update');
    }

    public function delete(User $user, Faq $faq): bool
    {
        return $user->can('cms.update');
    }
}
