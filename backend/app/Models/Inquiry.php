<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inquiry extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'property_id',
        'name',
        'email',
        'phone',
        'message',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Status constants
     */
    const STATUS_NEW = 'new';
    const STATUS_CONTACTED = 'contacted';
    const STATUS_FOLLOW_UP = 'follow_up';
    const STATUS_CLOSED = 'closed';

    // Users relation
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    /**
     * Get the property that owns the inquiry.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Scope a query to only include new inquiries.
     */
    public function scopeNew($query)
    {
        return $query->where('status', self::STATUS_NEW);
    }

    /**
     * Scope a query to only include contacted inquiries.
     */
    public function scopeContacted($query)
    {
        return $query->where('status', self::STATUS_CONTACTED);
    }

    /**
     * Scope a query to only include inquiries needing follow-up.
     */
    public function scopeFollowUp($query)
    {
        return $query->where('status', self::STATUS_FOLLOW_UP);
    }

    /**
     * Scope a query to only include closed inquiries.
     */
    public function scopeClosed($query)
    {
        return $query->where('status', self::STATUS_CLOSED);
    }

    /**
     * Scope a query to only include active inquiries (not closed).
     */
    public function scopeActive($query)
    {
        return $query->where('status', '!=', self::STATUS_CLOSED);
    }

    /**
     * Scope a query to order by priority (new first, then follow-up, then contacted).
     */
    public function scopeByPriority($query)
    {
        return $query->orderByRaw("
            CASE 
                WHEN status = 'new' THEN 1
                WHEN status = 'follow_up' THEN 2
                WHEN status = 'contacted' THEN 3
                ELSE 4
            END
        ")->orderBy('created_at', 'desc');
    }

    /**
     * Scope a query to only include inquiries for a specific property.
     */
    public function scopeForProperty($query, $propertyId)
    {
        return $query->where('property_id', $propertyId);
    }

    /**
     * Check if the inquiry is new.
     */
    public function getIsNewAttribute(): bool
    {
        return $this->status === self::STATUS_NEW;
    }

    /**
     * Check if the inquiry needs follow-up.
     */
    public function getNeedsFollowUpAttribute(): bool
    {
        return $this->status === self::STATUS_FOLLOW_UP;
    }

    /**
     * Check if the inquiry is active.
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->status !== self::STATUS_CLOSED;
    }

    /**
     * Get formatted created date.
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->created_at->format('M j, Y g:i A');
    }

    /**
     * Get the time since the inquiry was created.
     */
    public function getTimeSinceCreatedAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Mark inquiry as contacted.
     */
    public function markAsContacted(?string $notes = null): bool
    {
        if ($this->is_active) {
            $this->update([
                'status' => self::STATUS_CONTACTED,
                'message' => $notes ? ($this->message . "\n\nAgent Notes: " . $notes) : $this->message
            ]);
            return true;
        }

        return false;
    }

    /**
     * Mark inquiry as needing follow-up.
     */
    public function markForFollowUp(?string $reason = null): bool
    {
        if ($this->is_active) {
            $this->update([
                'status' => self::STATUS_FOLLOW_UP,
                'message' => $reason ? ($this->message . "\n\nFollow-up Needed: " . $reason) : $this->message
            ]);
            return true;
        }

        return false;
    }

    /**
     * Close the inquiry.
     */
    public function close(?string $resolution = null): bool
    {
        if ($this->is_active) {
            $this->update([
                'status' => self::STATUS_CLOSED,
                'message' => $resolution ? ($this->message . "\n\nClosed: " . $resolution) : $this->message
            ]);
            return true;
        }

        return false;
    }

    /**
     * Reopen a closed inquiry.
     */
    public function reopen(): bool
    {
        if (!$this->is_active) {
            $this->update(['status' => self::STATUS_NEW]);
            return true;
        }

        return false;
    }

    /**
     * Get inquiry statistics for a property or agent.
     */
    public static function getStatistics($propertyId = null, $agentId = null): array
    {
        $query = self::query();

        if ($propertyId) {
            $query->where('property_id', $propertyId);
        } elseif ($agentId) {
            $query->whereHas('property', function ($q) use ($agentId) {
                $q->where('agent_id', $agentId);
            });
        }

        $total = $query->count();
        $new = (clone $query)->new()->count();
        $contacted = (clone $query)->contacted()->count();
        $followUp = (clone $query)->followUp()->count();
        $closed = (clone $query)->closed()->count();

        return [
            'total' => $total,
            'new' => $new,
            'contacted' => $contacted,
            'follow_up' => $followUp,
            'closed' => $closed,
            'response_rate' => $total > 0 ? round((($contacted + $followUp) / $total) * 100, 2) : 0,
        ];
    }

    /**
     * Get recent inquiries with property and agent information.
     */
    public static function getRecentInquiries($limit = 10, $agentId = null)
    {
        $query = self::with(['property', 'property.agent'])
            ->active()
            ->byPriority()
            ->limit($limit);

        if ($agentId) {
            $query->whereHas('property', function ($q) use ($agentId) {
                $q->where('agent_id', $agentId);
            });
        }

        return $query->get();
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Send notification when new inquiry is created
        static::created(function ($inquiry) {
            // Here you can add notification logic
            // Example: Send email to agent, create notification, etc.
            if ($inquiry->property && $inquiry->property->agent) {
                // Notification::send($inquiry->property->agent, new NewInquiryNotification($inquiry));
            }
        });

        // Validate email format
        static::saving(function ($inquiry) {
            if (!filter_var($inquiry->email, FILTER_VALIDATE_EMAIL)) {
                throw new \Exception('Invalid email address format.');
            }
        });
    }

    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    public function getTable()
    {
        return 'inquiries';
    }

    /**
     * Get status display name.
     */
    public function getStatusDisplayAttribute(): string
    {
        return match ($this->status) {
            'new' => 'New',
            'contacted' => 'Contacted',
            'follow_up' => 'Follow Up',
            'closed' => 'Closed',
            default => 'Unknown',
        };
    }
}
