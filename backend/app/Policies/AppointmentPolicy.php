<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AppointmentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Users can view their own appointments
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Appointment $appointment): bool
    {
        return $user->id === $appointment->user_id || 
               $user->id === $appointment->agent_id ||
               $user->role === 'admin';
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role === 'client'; // Only clients can create appointments
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Appointment $appointment): bool
    {
        return $user->id === $appointment->user_id || 
               $user->id === $appointment->agent_id ||
               $user->role === 'admin';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Appointment $appointment): bool
    {
        return $user->id === $appointment->user_id || 
               $user->id === $appointment->agent_id ||
               $user->role === 'admin';
    }

    /**
     * Determine whether the user can confirm appointments.
     */
    public function confirm(User $user, Appointment $appointment): bool
    {
        return $user->id === $appointment->agent_id || $user->role === 'admin';
    }

    /**
     * Determine whether the user can cancel appointments.
     */
    public function cancel(User $user, Appointment $appointment): bool
    {
        return $this->update($user, $appointment);
    }

    /**
     * Determine whether the user can complete appointments.
     */
    public function complete(User $user, Appointment $appointment): bool
    {
        return $user->id === $appointment->agent_id || $user->role === 'admin';
    }
}