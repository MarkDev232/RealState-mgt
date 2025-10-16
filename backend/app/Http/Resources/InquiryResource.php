<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;


class InquiryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isAgent = auth::check() && auth::user()->role === 'agent';
        $isAdmin = auth::check() && auth::user()->role === 'admin';
        $canViewDetails = $isAgent || $isAdmin;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->when($canViewDetails, $this->email),
            'phone' => $this->when($canViewDetails, $this->phone),
            'message' => $this->when($canViewDetails, $this->message),
            'status' => $this->status,
            'status_display' => $this->status_display,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->when($this->deleted_at, $this->deleted_at),

            // Relationships
            'property' => new PropertyResource($this->whenLoaded('property')),

            // IDs for relationships
            'property_id' => $this->property_id,

            // Computed properties
            'is_new' => $this->is_new,
            'needs_follow_up' => $this->needs_follow_up,
            'is_active' => $this->is_active,
            'formatted_date' => $this->formatted_date,
            'time_since_created' => $this->time_since_created,
            'short_message' => $this->when(!$canViewDetails, function () {
                return strlen($this->message) > 100 
                    ? substr($this->message, 0, 100) . '...' 
                    : $this->message;
            }),

            // Status-specific information
            'status_info' => [
                'color' => $this->getStatusColor(),
                'icon' => $this->getStatusIcon(),
                'priority' => $this->getStatusPriority(),
                'description' => $this->getStatusDescription(),
            ],

            // Contact information (partial for privacy)
            'contact_info' => [
                'name' => $this->name,
                'email' => $this->when($canViewDetails, $this->email),
                'phone' => $this->when($canViewDetails, $this->phone),
                'initials' => $this->getContactInitials(),
            ],

            // Action permissions
            'permissions' => [
                'can_mark_contacted' => $this->is_active && $canViewDetails,
                'can_mark_follow_up' => $this->is_active && $canViewDetails,
                'can_close' => $this->is_active && $canViewDetails,
                'can_reopen' => !$this->is_active && $canViewDetails,
                'can_delete' => $isAdmin,
            ],

            // Response tracking (if implemented)
            'response_info' => [
                'has_responded' => $this->status !== 'new',
                'response_time' => $this->getResponseTime(),
                'last_contact' => $this->getLastContactDate(),
            ],

            // URLs
            'url' => route('inquiries.show', $this->id),
            'api_url' => route('api.inquiries.show', $this->id),
            'property_url' => route('properties.show', $this->property_id),
        ];
    }

    /**
     * Get status color for UI
     */
    private function getStatusColor(): string
    {
        return match($this->status) {
            'new' => 'blue',
            'contacted' => 'green',
            'follow_up' => 'orange',
            'closed' => 'gray',
            default => 'gray',
        };
    }

    /**
     * Get status icon for UI
     */
    private function getStatusIcon(): string
    {
        return match($this->status) {
            'new' => 'envelope',
            'contacted' => 'check-circle',
            'follow_up' => 'clock',
            'closed' => 'archive',
            default => 'question-mark',
        };
    }

    /**
     * Get status priority (for sorting)
     */
    private function getStatusPriority(): int
    {
        return match($this->status) {
            'new' => 1,
            'follow_up' => 2,
            'contacted' => 3,
            'closed' => 4,
            default => 5,
        };
    }

    /**
     * Get status description
     */
    private function getStatusDescription(): string
    {
        return match($this->status) {
            'new' => 'New inquiry - needs attention',
            'contacted' => 'Customer has been contacted',
            'follow_up' => 'Follow-up required',
            'closed' => 'Inquiry closed',
            default => 'Unknown status',
        };
    }

    /**
     * Get contact initials for avatar
     */
    private function getContactInitials(): string
    {
        $names = explode(' ', $this->name);
        $initials = '';
        
        if (count($names) >= 2) {
            $initials = strtoupper($names[0][0] . $names[1][0]);
        } else {
            $initials = strtoupper(substr($this->name, 0, 2));
        }
        
        return $initials;
    }

    /**
     * Get response time in hours
     */
    private function getResponseTime(): ?int
    {
        if ($this->status === 'new' || is_null($this->updated_at)) {
            return null;
        }

        return $this->created_at->diffInHours($this->updated_at);
    }

    /**
     * Get last contact date
     */
    private function getLastContactDate(): ?string
    {
        if ($this->status === 'new') {
            return null;
        }

        return $this->updated_at->format('M j, Y g:i A');
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
                'privacy_notice' => 'Contact information is restricted to authorized agents and administrators',
                'links' => [
                    'self' => route('api.inquiries.show', $this->id),
                    'property' => route('api.properties.show', $this->property_id),
                ],
            ],
        ];
    }
}