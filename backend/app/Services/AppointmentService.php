<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class AppointmentService
{
    /**
     * Get all appointments for admin.
     */
    public function getAllAppointments(array $filters = []): LengthAwarePaginator
    {
        $query = Appointment::with(['property.images', 'user', 'agent'])
            ->when(isset($filters['status']), function ($q) use ($filters) {
                return $q->where('status', $filters['status']);
            })
            ->when(isset($filters['date_from']), function ($q) use ($filters) {
                return $q->where('appointment_date', '>=', $filters['date_from']);
            })
            ->when(isset($filters['date_to']), function ($q) use ($filters) {
                return $q->where('appointment_date', '<=', $filters['date_to']);
            });

        return $query->latest()->paginate(15);
    }

    /**
     * Get appointments for agent.
     */
    public function getAgentAppointments(int $agentId, array $filters = []): LengthAwarePaginator
    {
        $query = Appointment::with(['property.images', 'user'])
            ->where('agent_id', $agentId)
            ->when(isset($filters['status']), function ($q) use ($filters) {
                return $q->where('status', $filters['status']);
            })
            ->when(isset($filters['date_from']), function ($q) use ($filters) {
                return $q->where('appointment_date', '>=', $filters['date_from']);
            })
            ->when(isset($filters['date_to']), function ($q) use ($filters) {
                return $q->where('appointment_date', '<=', $filters['date_to']);
            });

        return $query->latest()->paginate(15);
    }

    /**
     * Get appointments for user.
     */
    public function getUserAppointments(int $userId, array $filters = []): LengthAwarePaginator
    {
        $query = Appointment::with(['property.images', 'agent'])
            ->where('user_id', $userId)
            ->when(isset($filters['status']), function ($q) use ($filters) {
                return $q->where('status', $filters['status']);
            })
            ->when(isset($filters['date_from']), function ($q) use ($filters) {
                return $q->where('appointment_date', '>=', $filters['date_from']);
            })
            ->when(isset($filters['date_to']), function ($q) use ($filters) {
                return $q->where('appointment_date', '<=', $filters['date_to']);
            });

        return $query->latest()->paginate(15);
    }

    /**
     * Create a new appointment.
     */
    public function createAppointment(array $data, User $user): Appointment
    {
        return DB::transaction(function () use ($data, $user) {
            // Set user_id if not provided
            if (!isset($data['user_id'])) {
                $data['user_id'] = $user->id;
            }

            // Check for scheduling conflicts
            if (Appointment::hasConflict($data['agent_id'], $data['appointment_date'])) {
                throw new \Exception('The selected time slot is not available. Please choose a different time.');
            }

            $appointment = Appointment::create($data);

            // Send notifications (would be implemented with Laravel notifications)
            // $appointment->property->agent->notify(new NewAppointmentNotification($appointment));

            return $appointment->load(['property.images', 'user', 'agent']);
        });
    }

    /**
     * Update an appointment.
     */
    public function updateAppointment(Appointment $appointment, array $data): Appointment
    {
        return DB::transaction(function () use ($appointment, $data) {
            // Check for scheduling conflicts if date/time is being updated
            if (isset($data['appointment_date']) && 
                $data['appointment_date'] != $appointment->appointment_date) {
                
                if (Appointment::hasConflict(
                    $data['agent_id'] ?? $appointment->agent_id, 
                    $data['appointment_date'],
                    $appointment->id
                )) {
                    throw new \Exception('The selected time slot is not available. Please choose a different time.');
                }
            }

            $appointment->update($data);

            return $appointment->fresh(['property.images', 'user', 'agent']);
        });
    }

    /**
     * Delete an appointment.
     */
    public function deleteAppointment(Appointment $appointment): bool
    {
        return DB::transaction(function () use ($appointment) {
            // Send cancellation notifications
            // $appointment->user->notify(new AppointmentCancelledNotification($appointment));
            
            return $appointment->delete();
        });
    }

    /**
     * Get available time slots for an agent.
     */
    public function getAvailableSlots(int $agentId, string $date): array
    {
        return Appointment::getAvailableSlots($agentId, $date);
    }

    /**
     * Get appointment statistics.
     */
    public function getStatistics(User $user): array
    {
        if ($user->role === 'admin') {
            $total = Appointment::count();
            $pending = Appointment::pending()->count();
            $confirmed = Appointment::confirmed()->count();
            $cancelled = Appointment::where('status', 'cancelled')->count();
            $completed = Appointment::where('status', 'completed')->count();
            $upcoming = Appointment::upcoming()->count();
        } elseif ($user->role === 'agent') {
            $total = Appointment::where('agent_id', $user->id)->count();
            $pending = Appointment::where('agent_id', $user->id)->pending()->count();
            $confirmed = Appointment::where('agent_id', $user->id)->confirmed()->count();
            $cancelled = Appointment::where('agent_id', $user->id)->where('status', 'cancelled')->count();
            $completed = Appointment::where('agent_id', $user->id)->where('status', 'completed')->count();
            $upcoming = Appointment::where('agent_id', $user->id)->upcoming()->count();
        } else {
            $total = Appointment::where('user_id', $user->id)->count();
            $pending = Appointment::where('user_id', $user->id)->pending()->count();
            $confirmed = Appointment::where('user_id', $user->id)->confirmed()->count();
            $cancelled = Appointment::where('user_id', $user->id)->where('status', 'cancelled')->count();
            $completed = Appointment::where('user_id', $user->id)->where('status', 'completed')->count();
            $upcoming = Appointment::where('user_id', $user->id)->upcoming()->count();
        }

        return [
            'total' => $total,
            'pending' => $pending,
            'confirmed' => $confirmed,
            'cancelled' => $cancelled,
            'completed' => $completed,
            'upcoming' => $upcoming,
        ];
    }

    /**
     * Get upcoming appointments.
     */
    public function getUpcomingAppointments(User $user, int $limit = 5)
    {
        if ($user->role === 'admin') {
            return Appointment::with(['property', 'user', 'agent'])
                ->upcoming()
                ->orderBy('appointment_date')
                ->limit($limit)
                ->get();
        } elseif ($user->role === 'agent') {
            return Appointment::with(['property', 'user'])
                ->where('agent_id', $user->id)
                ->upcoming()
                ->orderBy('appointment_date')
                ->limit($limit)
                ->get();
        } else {
            return Appointment::with(['property', 'agent'])
                ->where('user_id', $user->id)
                ->upcoming()
                ->orderBy('appointment_date')
                ->limit($limit)
                ->get();
        }
    }
}