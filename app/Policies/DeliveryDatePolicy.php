<?php

namespace App\Policies;

use App\Models\User;
use App\Models\DeliveryDate;
use Illuminate\Auth\Access\HandlesAuthorization;

class DeliveryDatePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('settings.manage');
    }

    public function view(User $user, DeliveryDate $deliveryDate): bool
    {
        return $user->can('settings.manage');
    }

    public function create(User $user): bool
    {
        return $user->can('settings.manage');
    }

    public function update(User $user, DeliveryDate $deliveryDate): bool
    {
        return $user->can('settings.manage');
    }

    public function delete(User $user, DeliveryDate $deliveryDate): bool
    {
        return $user->can('settings.manage');
    }
}
