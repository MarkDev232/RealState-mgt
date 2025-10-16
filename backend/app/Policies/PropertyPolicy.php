<?php

namespace App\Policies;

use App\Models\Property;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PropertyPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Everyone can view properties
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Property $property): bool
    {
        return true; // Everyone can view individual properties
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'agent']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Property $property): bool
    {
        return $user->role === 'admin' || 
               ($user->role === 'agent' && $property->agent_id === $user->id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Property $property): bool
    {
        return $user->role === 'admin' || 
               ($user->role === 'agent' && $property->agent_id === $user->id);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Property $property): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Property $property): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can update property status.
     */
    public function updateStatus(User $user, Property $property): bool
    {
        return $this->update($user, $property);
    }

    /**
     * Determine whether the user can toggle featured status.
     */
    public function toggleFeatured(User $user, Property $property): bool
    {
        return $user->role === 'admin' || 
               ($user->role === 'agent' && $property->agent_id === $user->id);
    }
}