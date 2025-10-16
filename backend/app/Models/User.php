<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'address',
        'avatar',
        'is_active'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean'
    ];

    // Relationships
    public function properties()
    {
        return $this->hasMany(Property::class, 'agent_id');
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    // In User model
    public function inquiries()
    {
        return $this->hasMany(Inquiry::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function agentAppointments()
    {
        return $this->hasMany(Appointment::class, 'agent_id');
    }

    // Scopes
    public function scopeAgents($query)
    {
        return $query->where('role', 'agent')->where('is_active', true);
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isAgent()
    {
        return $this->role === 'agent';
    }

    public function isClient()
    {
        return $this->role === 'client';
    }

    public function getRoleDisplayAttribute(): string
    {
        return match ($this->role) {
            'admin' => 'Administrator',
            'agent' => 'Real Estate Agent',
            'client' => 'Client',
            default => 'Unknown',
        };
    }

    /**
     * Get avatar URL.
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }

        // Generate initials avatar as fallback
        return $this->generateInitialsAvatar();
    }

    /**
     * Get user initials.
     */
    public function getInitialsAttribute(): string
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
     * Generate initials avatar URL.
     */
    private function generateInitialsAvatar(): string
    {
        // You can use a service like UI Avatars or generate locally
        $initials = $this->initials;
        $backgroundColor = '4F46E5'; // Example color
        $color = 'FFFFFF';

        return "https://ui-avatars.com/api/?name={$initials}&background={$backgroundColor}&color={$color}&size=128";
    }
}
