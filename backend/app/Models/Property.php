<?php
// app/Models/Property.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

/** @method \Illuminate\Contracts\Auth\Guard check() */

class Property extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'agent_id',
        'title',
        'description',
        'address',
        'city',
        'state',
        'zip_code',
        'country',
        'price',
        'bedrooms',
        'bathrooms',
        'square_feet',
        'lot_size',
        'property_type',
        'status',
        'listing_type',
        'year_built',
        'amenities',
        'images',
        'featured'
    ];

    protected $casts = [
        'amenities' => 'array',
        'images' => 'array',
        'price' => 'decimal:2',
        'featured' => 'boolean',
        'bedrooms' => 'integer',
        'bathrooms' => 'integer',
        'square_feet' => 'integer',
        'lot_size' => 'integer',
        'year_built' => 'integer'
    ];

    // Relationships
    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function images()
    {
        return $this->hasMany(PropertyImage::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function inquiries()
    {
        return $this->hasMany(Inquiry::class);
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeForSale($query)
    {
        return $query->where('listing_type', 'sale');
    }

    public function scopeForRent($query)
    {
        return $query->where('listing_type', 'rent');
    }

    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhere('address', 'like', "%{$search}%")
                ->orWhere('city', 'like', "%{$search}%");
        });
    }

    // Accessors
    public function getPrimaryImageAttribute()
    {
        // Always use the relationship query to avoid issues
        $primary = $this->images()->where('is_primary', true)->first();

        return $primary?->image_path
            ?? $this->images()->first()?->image_path
            ?? 'default.jpg';
    }

    public function getIsFavoriteAttribute()
    {
        if (!Auth::check()) return false;
        return $this->favorites()->where('user_id', Auth::id())->exists();
    }
    /**
     * Get formatted price attribute.
     */
    public function getFormattedPriceAttribute(): string
    {
        if ($this->price === null) {
            return 'Price not set';
        }

        return '$' . number_format((float) $this->price);
    }

    /**
     * Get property type display name.
     */
    public function getPropertyTypeDisplayAttribute(): string
    {
        return match ($this->property_type) {
            'house' => 'House',
            'apartment' => 'Apartment',
            'condo' => 'Condo',
            'townhouse' => 'Townhouse',
            'land' => 'Land',
            'commercial' => 'Commercial',
            default => 'Unknown',
        };
    }

    /**
     * Get status display name.
     */
    public function getStatusDisplayAttribute(): string
    {
        return match ($this->status) {
            'available' => 'Available',
            'sold' => 'Sold',
            'pending' => 'Pending',
            'rented' => 'Rented',
            default => 'Unknown',
        };
    }

    /**
     * Get listing type display name.
     */
    public function getListingTypeDisplayAttribute(): string
    {
        return match ($this->listing_type) {
            'sale' => 'For Sale',
            'rent' => 'For Rent',
            default => 'Unknown',
        };
    }
}
