<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FavoriteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'property_id' => $this->property_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Relationships
            'property' => new PropertyResource($this->whenLoaded('property')),
            'user' => new UserResource($this->whenLoaded('user')),

            // Additional metadata
            'favorited_at' => $this->created_at->format('M j, Y g:i A'),
            'favorited_time_ago' => $this->created_at->diffForHumans(),

            // URLs
            'api_url' => route('api.favorites.show', $this->id),
        ];
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
            ],
        ];
    }
}