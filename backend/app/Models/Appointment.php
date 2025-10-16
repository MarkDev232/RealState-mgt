<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Appointment extends Model
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
        'agent_id',
        'appointment_date',
        'status',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'appointment_date' => 'datetime',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array<int, string>
     */
    protected $dates = [
        'appointment_date',
        'created_at',
        'updated_at',
    ];

    /**
     * Status constants
     */
    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_COMPLETED = 'completed';

    /**
     * Get the user who booked the appointment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the property for the appointment.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Get the agent for the appointment.
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    /**
     * Scope a query to only include pending appointments.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope a query to only include confirmed appointments.
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', self::STATUS_CONFIRMED);
    }

    /**
     * Scope a query to only include upcoming appointments.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('appointment_date', '>', now())
            ->whereIn('status', [self::STATUS_PENDING, self::STATUS_CONFIRMED]);
    }

    /**
     * Scope a query to only include past appointments.
     */
    public function scopePast($query)
    {
        return $query->where('appointment_date', '<', now());
    }

    /**
     * Scope a query to only include appointments for a specific agent.
     */
    public function scopeForAgent($query, $agentId)
    {
        return $query->where('agent_id', $agentId);
    }

    /**
     * Scope a query to only include appointments for a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Check if the appointment is upcoming.
     */
    public function getIsUpcomingAttribute(): bool
    {
        return $this->appointment_date > now() &&
            in_array($this->status, [self::STATUS_PENDING, self::STATUS_CONFIRMED]);
    }

    /**
     * Check if the appointment is past.
     */
    public function getIsPastAttribute(): bool
    {
        return $this->appointment_date < now();
    }

    /**
     * Check if the appointment can be cancelled.
     */
    public function getCanBeCancelledAttribute(): bool
    {
        return $this->is_upcoming && in_array($this->status, [self::STATUS_PENDING, self::STATUS_CONFIRMED]);
    }

    /**
     * Check if the appointment can be confirmed.
     */
    public function getCanBeConfirmedAttribute(): bool
    {
        return $this->status === self::STATUS_PENDING && $this->is_upcoming;
    }

    /**
     * Get the time until the appointment.
     */
    public function getTimeUntilAppointmentAttribute(): string
    {
        return $this->appointment_date->diffForHumans();
    }

    /**
     * Confirm the appointment.
     */
    public function confirm(): bool
    {
        if ($this->can_be_confirmed) {
            $this->update(['status' => self::STATUS_CONFIRMED]);
            return true;
        }

        return false;
    }

    /**
     * Cancel the appointment.
     */
    public function cancel(?string $reason = null): bool
    {
        if ($this->can_be_cancelled) {
            $this->update([
                'status' => self::STATUS_CANCELLED,
                'notes' => $reason ? ($this->notes ? $this->notes . "\n\nCancelled: " . $reason : "Cancelled: " . $reason) : $this->notes
            ]);
            return true;
        }

        return false;
    }

    /**
     * Mark appointment as completed.
     */
    public function complete(): bool
    {
        if ($this->is_past && $this->status === self::STATUS_CONFIRMED) {
            $this->update(['status' => self::STATUS_COMPLETED]);
            return true;
        }

        return false;
    }

    /**
     * Check for scheduling conflicts.
     */
    public static function hasConflict($agentId, $appointmentDate, $excludeId = null): bool
    {
        $query = self::where('agent_id', $agentId)
            ->where('appointment_date', $appointmentDate)
            ->whereIn('status', [self::STATUS_PENDING, self::STATUS_CONFIRMED]);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Get available time slots for an agent on a specific date.
     */
    public static function getAvailableSlots($agentId, $date): array
    {
        $date = Carbon::parse($date);
        $slots = [];

        // Generate time slots from 9 AM to 6 PM
        $startTime = $date->copy()->setHour(9)->setMinute(0);
        $endTime = $date->copy()->setHour(18)->setMinute(0);

        $currentTime = $startTime->copy();

        while ($currentTime < $endTime) {
            // Check if slot is available (no conflict)
            $hasConflict = self::hasConflict($agentId, $currentTime);

            if (!$hasConflict && $currentTime > now()) {
                $slots[] = [
                    'time' => $currentTime->format('H:i'),
                    'datetime' => $currentTime->toDateTimeString(),
                    'available' => true,
                ];
            }

            $currentTime->addMinutes(30); // 30-minute intervals
        }

        return $slots;
    }

    /**
     * Boot the model.
     */
    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Set agent_id from property if not provided
        static::creating(function ($appointment) {
            if (empty($appointment->agent_id) && $appointment->property) {
                $appointment->agent_id = $appointment->property->agent_id;
            }
        });

        // Validate appointment date is in the future for new appointments
        static::saving(function ($appointment) {
            // Allow past dates for completed or cancelled appointments
            if (
                $appointment->appointment_date < now() &&
                !in_array($appointment->status, [self::STATUS_COMPLETED, self::STATUS_CANCELLED])
            ) {
                throw new \Exception('Appointment date must be in the future for pending or confirmed appointments.');
            }
        });
    }

    /**
     * Get formatted date attribute.
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->appointment_date->format('M j, Y g:i A');
    }

    /**
     * Get status display name.
     */
    public function getStatusDisplayAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Pending',
            'confirmed' => 'Confirmed',
            'cancelled' => 'Cancelled',
            'completed' => 'Completed',
            default => 'Unknown',
        };
    }
}
