<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isOwner = Auth::check() && Auth::id() === $this->id;
        $isAdmin = Auth::check() && Auth::user()->role === 'admin';

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->when($isOwner || $isAdmin, $this->email),
            'role' => $this->role,
            'role_display' => $this->role_display,
            'phone' => $this->when($isOwner || $isAdmin, $this->phone),
            'address' => $this->when($isOwner || $isAdmin, $this->address),
            'avatar' => $this->avatar_url,
            'is_active' => $this->when($isAdmin, $this->is_active),
            'email_verified_at' => $this->when($isOwner || $isAdmin, $this->email_verified_at),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Relationships
            'properties' => PropertyResource::collection($this->whenLoaded('properties')),
            'favorites' => PropertyResource::collection($this->whenLoaded('favorites')),
            'appointments' => AppointmentResource::collection($this->whenLoaded('appointments')),
            'agent_appointments' => AppointmentResource::collection($this->whenLoaded('agentAppointments')),

            // Counts
            'properties_count' => $this->whenCounted('properties'),
            'favorites_count' => $this->whenCounted('favorites'),
            'appointments_count' => $this->whenCounted('appointments'),
            'agent_appointments_count' => $this->whenCounted('agentAppointments'),

            // Agent-specific data
            'agent_profile' => $this->when($this->role === 'agent', function () {
                return [
                    'total_listings' => $this->properties_count ?? $this->properties()->count(),
                    'active_listings' => $this->properties()->available()->count(),
                    'sold_listings' => $this->properties()->where('status', 'sold')->count(),
                    'rating' => $this->agent_rating ?? null,
                    'experience' => $this->agent_experience ?? null,
                ];
            }),

            // Client-specific data
            'client_profile' => $this->when($this->role === 'client', function () {
                return [
                    'total_favorites' => $this->favorites_count ?? $this->favorites()->count(),
                    'total_appointments' => $this->appointments_count ?? $this->appointments()->count(),
                    'preferred_property_types' => $this->preferred_types ?? null,
                    'budget_range' => $this->budget_range ?? null,
                ];
            }),

            // Computed properties
            'initials' => $this->initials,
            'has_avatar' => !empty($this->avatar),
            'is_verified' => !is_null($this->email_verified_at),
            'member_since' => $this->created_at->diffForHumans(),

            // URLs - Use API routes or remove if not needed
            'profile_url' => url("/api/users/{$this->id}"), // Use direct URL or API route
            'avatar_url' => $this->avatar_url,
        ];
    }

    /**
     * Customize the outgoing response for the resource.
     */
    public function withResponse($request, $response)
    {
        $response->header('X-Resource-Type', 'User');
        $response->header('X-Resource-ID', $this->id);
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
                'links' => [
                    'self' => url("/api/users/{$this->id}"), // Use direct URL
                    'profile' => url("/api/users/{$this->id}"), // Use same as self or remove
                ],
            ],
        ];
    }
}