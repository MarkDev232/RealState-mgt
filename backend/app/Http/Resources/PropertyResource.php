<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'zip_code' => $this->zip_code,
            'country' => $this->country,
            'price' => $this->price,
            'formatted_price' => $this->formatted_price,
            'bedrooms' => $this->bedrooms,
            'bathrooms' => $this->bathrooms,
            'square_feet' => $this->square_feet,
            'formatted_square_feet' => $this->square_feet ? number_format($this->square_feet) . ' sq ft' : null,
            'lot_size' => $this->lot_size,
            'formatted_lot_size' => $this->lot_size ? number_format($this->lot_size) . ' sq ft' : null,
            'property_type' => $this->property_type,
            'property_type_display' => $this->property_type_display,
            'status' => $this->status,
            'status_display' => $this->status_display,
            'listing_type' => $this->listing_type,
            'listing_type_display' => $this->listing_type_display,
            'year_built' => $this->year_built,
            'amenities' => $this->amenities ?? [],
            'featured' => $this->featured,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->when($this->deleted_at, $this->deleted_at),

            // Relationships
            'agent' => new UserResource($this->whenLoaded('agent')),
            'images' => PropertyImageResource::collection($this->whenLoaded('images')),
            'primary_image' => $this->when($this->relationLoaded('images'), function () {
                $primary = $this->images->where('is_primary', true)->first();
                return $primary ? new PropertyImageResource($primary) : 
                       ($this->images->first() ? new PropertyImageResource($this->images->first()) : null);
            }),
            'image_urls' => $this->when($this->relationLoaded('images'), function () {
                return $this->images->map(function ($image) {
                    return $image->image_url;
                });
            }),

            // Counts
            'favorites_count' => $this->whenCounted('favorites'),
            'appointments_count' => $this->whenCounted('appointments'),
            'inquiries_count' => $this->whenCounted('inquiries'),

            // User-specific data
            'is_favorite' => $this->when(auth()->check(), function () {
                return $this->favorites->contains('user_id', auth()->id());
            }),

            // Additional computed properties
            'location' => $this->city . ', ' . $this->state,
            'full_address' => $this->address . ', ' . $this->city . ', ' . $this->state . ' ' . $this->zip_code,
            'price_per_sqft' => $this->when($this->square_feet && $this->square_feet > 0, function () {
                return round($this->price / $this->square_feet, 2);
            }),

            // URLs
            'url' => route('properties.show', $this->id),
            'api_url' => route('api.properties.show', $this->id),
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
                'copyright' => 'Real Estate Management System',
                'authors' => [
                    'Your Team Name'
                ],
            ],
        ];
    }
}