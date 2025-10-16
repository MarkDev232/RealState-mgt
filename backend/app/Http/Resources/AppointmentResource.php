<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class AppointmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    /** @var \Illuminate\Contracts\Auth\Guard|\Illuminate\Contracts\Auth\StatefulGuard $auth */

    public function toArray(Request $request): array
    {
        $isOwner = Auth::check() && (Auth::id() === $this->user_id || Auth::id() === $this->agent_id);
        $isAdmin = Auth::check() && Auth::user()?->role === 'admin';

        return [
            'id' => $this->id,
            'appointment_date' => $this->appointment_date,
            'formatted_date' => $this->formatted_date,
            'time_until_appointment' => $this->time_until_appointment,
            'status' => $this->status,
            'status_display' => $this->status_display,
            'notes' => $this->when($isOwner || $isAdmin, $this->notes),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Relationships
            'user' => new UserResource($this->whenLoaded('user')),
            'property' => new PropertyResource($this->whenLoaded('property')),
            'agent' => new UserResource($this->whenLoaded('agent')),

            // IDs for relationships
            'user_id' => $this->user_id,
            'property_id' => $this->property_id,
            'agent_id' => $this->agent_id,

            // Computed properties
            'is_upcoming' => $this->is_upcoming,
            'is_past' => $this->is_past,
            'can_be_cancelled' => $this->can_be_cancelled,
            'can_be_confirmed' => $this->can_be_confirmed,
            'duration' => '30 minutes', // Default duration, can be made dynamic

            // Status-specific information
            'status_info' => [
                'color' => $this->getStatusColor(),
                'icon' => $this->getStatusIcon(),
                'description' => $this->getStatusDescription(),
            ],

            // Action permissions
            'permissions' => [
                'can_confirm' => $this->can_be_confirmed && (auth::id() === $this->agent_id || $isAdmin),
                'can_cancel' => $this->can_be_cancelled && $isOwner,
                'can_complete' => $this->is_past && $this->status === 'confirmed' && (auth::id() === $this->agent_id || $isAdmin),
                'can_reschedule' => $this->is_upcoming && $this->status !== 'cancelled' && $isOwner,
            ],

            // Additional metadata
            'time_slot' => [
                'start' => $this->appointment_date->toISOString(),
                'end' => $this->appointment_date->addMinutes(30)->toISOString(), // 30-minute slots
                'duration_minutes' => 30,
            ],

            // URLs
            'url' => route('appointments.show', $this->id),
            'api_url' => route('api.appointments.show', $this->id),
        ];
    }

    /**
     * Get status color for UI
     */
    private function getStatusColor(): string
    {
        return match ($this->status) {
            'pending' => 'yellow',
            'confirmed' => 'green',
            'cancelled' => 'red',
            'completed' => 'blue',
            default => 'gray',
        };
    }

    /**
     * Get status icon for UI
     */
    private function getStatusIcon(): string
    {
        return match ($this->status) {
            'pending' => 'clock',
            'confirmed' => 'check-circle',
            'cancelled' => 'x-circle',
            'completed' => 'check-circle',
            default => 'question-mark',
        };
    }

    /**
     * Get status description
     */
    private function getStatusDescription(): string
    {
        return match ($this->status) {
            'pending' => 'Waiting for agent confirmation',
            'confirmed' => 'Appointment confirmed',
            'cancelled' => 'Appointment cancelled',
            'completed' => 'Appointment completed',
            default => 'Unknown status',
        };
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'version' => '1.0.0',
                'api_version' => 'v1',
                'timestamp' => now()->toISOString(),
                'timezone' => config('app.timezone'),
                'links' => [
                    'self' => route('api.appointments.show', $this->id),
                    'property' => route('api.properties.show', $this->property_id),
                    'user' => $this->user_id ? route('api.users.show', $this->user_id) : null,
                    'agent' => $this->agent_id ? route('api.users.show', $this->agent_id) : null,
                ],
            ],
        ];
    }
}
