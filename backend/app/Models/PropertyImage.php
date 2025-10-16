<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyImage extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'property_id',
        'image_path',
        'is_primary',
        'order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_primary' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Get the property that owns the image.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Get the full image URL.
     */
    public function getImageUrlAttribute(): string
    {
        if (filter_var($this->image_path, FILTER_VALIDATE_URL)) {
            return $this->image_path;
        }

        return asset('storage/' . $this->image_path);
    }

    /**
     * Scope a query to only include primary images.
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Scope a query to order by display order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('id');
    }

    /**
     * Set the image as primary and update others.
     */
    public function setAsPrimary(): void
    {
        // Remove primary status from other images of the same property
        self::where('property_id', $this->property_id)
            ->where('id', '!=', $this->id)
            ->update(['is_primary' => false]);

        // Set this image as primary
        $this->update(['is_primary' => true]);
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Set order automatically if not provided
        static::creating(function ($image) {
            if (empty($image->order)) {
                $maxOrder = self::where('property_id', $image->property_id)->max('order');
                $image->order = $maxOrder ? $maxOrder + 1 : 1;
            }
        });

        // Set first image as primary if no primary exists
        static::created(function ($image) {
            $hasPrimary = self::where('property_id', $image->property_id)
                ->where('is_primary', true)
                ->exists();
            
            if (!$hasPrimary) {
                $image->setAsPrimary();
            }
        });
    }
}