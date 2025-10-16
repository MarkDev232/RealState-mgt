<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Favorite extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'property_id',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'favorites';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * Get the user that owns the favorite.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the property that is favorited.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Scope a query to only include favorites for a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to only include favorites for a specific property.
     */
    public function scopeForProperty($query, $propertyId)
    {
        return $query->where('property_id', $propertyId);
    }

    /**
     * Check if a favorite exists for user and property.
     */
    public static function exists($userId, $propertyId): bool
    {
        return self::where('user_id', $userId)
            ->where('property_id', $propertyId)
            ->exists($userId,$propertyId);
    }

    /**
     * Toggle favorite status for a user and property.
     */
    public static function toggle($userId, $propertyId): bool
    {
        $favorite = self::where('user_id', $userId)
            ->where('property_id', $propertyId)
            ->first();

        if ($favorite) {
            $favorite->delete();
            return false; // Removed from favorites
        }

        self::create([
            'user_id' => $userId,
            'property_id' => $propertyId,
        ]);

        return true; // Added to favorites
    }

    /**
     * Get favorite count for a property.
     */
    public static function countForProperty($propertyId): int
    {
        return self::where('property_id', $propertyId)->count();
    }

    /**
     * Get user's favorite properties with relationships.
     */
    public static function getUserFavorites($userId)
    {
        return self::with(['property.images', 'property.agent'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Ensure unique favorite per user and property
        static::creating(function ($favorite) {
            $exists = self::where('user_id', $favorite->user_id)
                ->where('property_id', $favorite->property_id)
                ->exists($favorite->user_id,$favorite->property_id);

            if ($exists) {
                return false; // Prevent duplicate favorites
            }
        });
    }
}