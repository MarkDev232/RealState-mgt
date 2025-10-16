<?php

namespace App\Services;

use App\Models\Property;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PropertyService
{
    /**
     * Get all properties with filters.
     */
    public function getAllProperties(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Property::with(['agent', 'images'])
            ->when(isset($filters['search']), function ($q) use ($filters) {
                return $q->search($filters['search']);
            })
            ->when(isset($filters['type']), function ($q) use ($filters) {
                return $q->where('property_type', $filters['type']);
            })
            ->when(isset($filters['listing_type']), function ($q) use ($filters) {
                return $q->where('listing_type', $filters['listing_type']);
            })
            ->when(isset($filters['min_price']), function ($q) use ($filters) {
                return $q->where('price', '>=', $filters['min_price']);
            })
            ->when(isset($filters['max_price']), function ($q) use ($filters) {
                return $q->where('price', '<=', $filters['max_price']);
            })
            ->when(isset($filters['bedrooms']), function ($q) use ($filters) {
                return $q->where('bedrooms', '>=', $filters['bedrooms']);
            })
            ->when(isset($filters['bathrooms']), function ($q) use ($filters) {
                return $q->where('bathrooms', '>=', $filters['bathrooms']);
            })
            ->when(isset($filters['city']), function ($q) use ($filters) {
                return $q->where('city', 'like', "%{$filters['city']}%");
            })
            ->when(isset($filters['state']), function ($q) use ($filters) {
                return $q->where('state', $filters['state']);
            })
            ->when(isset($filters['featured']), function ($q) use ($filters) {
                return $q->featured();
            })
            ->when(isset($filters['status']), function ($q) use ($filters) {
                return $q->where('status', $filters['status']);
            });

        return $query->latest()->paginate($perPage);
    }

    /**
     * Create a new property.
     */
    public function createProperty(array $data, User $agent): Property
    {
        return DB::transaction(function () use ($data, $agent) {
            $data['agent_id'] = $agent->id;
            
            // Handle amenities array
            if (isset($data['amenities']) && is_array($data['amenities'])) {
                $data['amenities'] = json_encode($data['amenities']);
            }

            $property = Property::create($data);

            // Handle image uploads
            if (isset($data['images'])) {
                $this->processPropertyImages($property, $data['images']);
            }

            return $property->load(['agent', 'images']);
        });
    }

    /**
     * Update an existing property.
     */
    public function updateProperty(Property $property, array $data): Property
    {
        return DB::transaction(function () use ($property, $data) {
            // Handle amenities array
            if (isset($data['amenities']) && is_array($data['amenities'])) {
                $data['amenities'] = json_encode($data['amenities']);
            }

            $property->update($data);

            // Handle image uploads
            if (isset($data['images'])) {
                $this->processPropertyImages($property, $data['images']);
            }

            return $property->fresh(['agent', 'images']);
        });
    }

    /**
     * Delete a property.
     */
    public function deleteProperty(Property $property): bool
    {
        return DB::transaction(function () use ($property) {
            // Delete associated images from storage
            foreach ($property->images as $image) {
                Storage::delete($image->image_path);
                $image->delete();
            }

            // Delete favorites and appointments
            $property->favorites()->delete();
            $property->appointments()->delete();
            $property->inquiries()->delete();

            return $property->delete();
        });
    }

    /**
     * Process and store property images.
     */
    private function processPropertyImages(Property $property, array $images): void
    {
        foreach ($images as $index => $image) {
            if ($image->isValid()) {
                $path = $image->store('properties/' . $property->id, 'public');
                
                $property->images()->create([
                    'image_path' => $path,
                    'order' => $index,
                    'is_primary' => $index === 0, // First image is primary by default
                ]);
            }
        }
    }

    /**
     * Get featured properties.
     */
    public function getFeaturedProperties(int $limit = 6)
    {
        return Property::with(['agent', 'images'])
            ->featured()
            ->available()
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get properties by agent.
     */
    public function getPropertiesByAgent(User $agent, array $filters = [])
    {
        $query = $agent->properties()->with(['images', 'appointments', 'inquiries']);

        // Apply filters
        $query->when(isset($filters['status']), function ($q) use ($filters) {
            return $q->where('status', $filters['status']);
        })
        ->when(isset($filters['type']), function ($q) use ($filters) {
            return $q->where('property_type', $filters['type']);
        })
        ->when(isset($filters['featured']), function ($q) use ($filters) {
            return $q->where('featured', $filters['featured']);
        });

        return $query->latest()->paginate(15);
    }

    /**
     * Get property statistics for dashboard.
     */
    public function getStatistics(User $user): array
    {
        if ($user->role === 'admin') {
            $total = Property::count();
            $available = Property::available()->count();
            $sold = Property::where('status', 'sold')->count();
            $pending = Property::where('status', 'pending')->count();
            $rented = Property::where('status', 'rented')->count();
            $featured = Property::featured()->count();
        } else {
            $total = $user->properties()->count();
            $available = $user->properties()->available()->count();
            $sold = $user->properties()->where('status', 'sold')->count();
            $pending = $user->properties()->where('status', 'pending')->count();
            $rented = $user->properties()->where('status', 'rented')->count();
            $featured = $user->properties()->featured()->count();
        }

        return [
            'total' => $total,
            'available' => $available,
            'sold' => $sold,
            'pending' => $pending,
            'rented' => $rented,
            'featured' => $featured,
        ];
    }

    /**
     * Search properties by location.
     */
    public function searchByLocation(string $location, int $perPage = 15)
    {
        return Property::with(['agent', 'images'])
            ->where(function ($query) use ($location) {
                $query->where('address', 'like', "%{$location}%")
                      ->orWhere('city', 'like', "%{$location}%")
                      ->orWhere('state', 'like', "%{$location}%")
                      ->orWhere('zip_code', 'like', "%{$location}%");
            })
            ->available()
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get similar properties.
     */
    public function getSimilarProperties(Property $property, int $limit = 4)
    {
        return Property::with(['agent', 'images'])
            ->where('id', '!=', $property->id)
            ->where('property_type', $property->property_type)
            ->where('listing_type', $property->listing_type)
            ->where('city', $property->city)
            ->available()
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }
}