<?php

namespace App\Policies;

use App\Models\User;
use App\Models\DeliveryPostcode;
use Illuminate\Auth\Access\HandlesAuthorization;

class DeliveryPostcodePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('settings.manage');
    }

    public function view(User $user, DeliveryPostcode $deliveryPostcode): bool
    {
        return $user->can('settings.manage');
    }

    public function create(User $user): bool
    {
        return $user->can('settings.manage');
    }

    public function update(User $user, DeliveryPostcode $deliveryPostcode): bool
    {
        return $user->can('settings.manage');
    }

    public function delete(User $user, DeliveryPostcode $deliveryPostcode): bool
    {
        return $user->can('settings.manage');
    }
}
