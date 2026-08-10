<?php

namespace App\Policies;

use App\Models\User;
use App\Models\DeliverySession;
use Illuminate\Auth\Access\HandlesAuthorization;

class DeliverySessionPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('delivery.manage') 
            || $user->can('delivery.driver') 
            || $user->hasRole('Staff') 
            || $user->role === 'staff';
    }

    public function view(User $user, DeliverySession $deliverySession): bool
    {
        if ($user->can('delivery.manage')) {
            return true;
        }

        return $deliverySession->staff_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->can('delivery.manage') 
            || $user->can('delivery.driver') 
            || $user->hasRole('Staff') 
            || $user->role === 'staff';
    }

    public function update(User $user, DeliverySession $deliverySession): bool
    {
        if ($user->can('delivery.manage')) {
            return true;
        }

        return $deliverySession->staff_id === $user->id;
    }

    public function delete(User $user, DeliverySession $deliverySession): bool
    {
        if ($user->can('delivery.manage')) {
            return true;
        }

        return $deliverySession->staff_id === $user->id;
    }
}
