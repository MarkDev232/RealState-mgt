<?php

namespace App\Services;

use App\Models\Inquiry;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserService
{
    /**
     * Get all users with filters.
     */
    public function getAllUsers(array $filters = []): LengthAwarePaginator
    {
        $query = User::withCount(['properties', 'appointments','inquiries']) 
            ->when(isset($filters['role']), function ($q) use ($filters) {
                return $q->where('role', $filters['role']);
            })
            ->when(isset($filters['status']), function ($q) use ($filters) {
                return $q->where('is_active', $filters['status'] === 'active');
            })
            ->when(isset($filters['search']), function ($q) use ($filters) {
                return $q->where(function ($query) use ($filters) {
                    $query->where('name', 'like', "%{$filters['search']}%")
                        ->orWhere('email', 'like', "%{$filters['search']}%");
                });
            });

        return $query->latest()->paginate(10);
    }

    /**
     * Update user profile.
     */
    public function updateProfile(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            $user->update($data);

            return $user->fresh();
        });
    }

    /**
     * Update user avatar.
     */
    public function updateAvatar(User $user, $avatarFile): User
    {
        return DB::transaction(function () use ($user, $avatarFile) {
            // Delete old avatar if exists
            if ($user->avatar) {
                Storage::delete($user->avatar);
            }

            // Store new avatar
            $path = $avatarFile->store('avatars', 'public');

            $user->update(['avatar' => $path]);

            return $user->fresh();
        });
    }

    /**
     * Delete user avatar.
     */
    public function deleteAvatar(User $user): User
    {
        return DB::transaction(function () use ($user) {
            if ($user->avatar) {
                Storage::delete($user->avatar);
                $user->update(['avatar' => null]);
            }

            return $user->fresh();
        });
    }

    /**
     * Update user role.
     */
    public function updateRole(User $user, string $role): User
    {
        return DB::transaction(function () use ($user, $role) {
            $user->update(['role' => $role]);

            return $user->fresh();
        });
    }

    /**
     * Toggle user active status.
     */
    public function toggleActive(User $user): User
    {
        return DB::transaction(function () use ($user) {
            $user->update(['is_active' => !$user->is_active]);

            return $user->fresh();
        });
    }

    /**
     * Change user password.
     */
    public function changePassword(User $user, string $newPassword): bool
    {
        return $user->update([
            'password' => Hash::make($newPassword),
        ]);
    }

    /**
     * Get user statistics.
     */
    public function getStatistics(User $user): array
    {
        if ($user->role === 'admin') {
            $totalUsers = User::count();
            $activeUsers = User::where('is_active', true)->count();
            $agents = User::where('role', 'agent')->count();
            $clients = User::where('role', 'client')->count();
            $admins = User::where('role', 'admin')->count();
        } else {
            $totalUsers = 0;
            $activeUsers = 0;
            $agents = 0;
            $clients = 0;
            $admins = 0;
        }

        // User-specific statistics
        $userProperties = $user->properties()->count();
        $userAppointments = $user->appointments()->count();
        $userInquiries = $user->role === 'agent' ?
            \App\Models\Inquiry::whereHas('property', function ($q) use ($user) {
                $q->where('agent_id', $user->id);
            })->count() : 0;

        return [
            'total_users' => $totalUsers,
            'active_users' => $activeUsers,
            'agents' => $agents,
            'clients' => $clients,
            'admins' => $admins,
            'user_properties' => $userProperties,
            'user_appointments' => $userAppointments,
            'user_inquiries' => $userInquiries,
        ];
    }

    /**
     * Get agents list.
     */
    public function getAgents()
    {
        return User::where('role', 'agent')
            ->where('is_active', true)
            ->withCount('properties')
            ->orderBy('name')
            ->get();
    }

    /**
     * Create a new user.
     */
    public function createUser(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password'] ?? 'password'),
                'role' => $data['role'] ?? 'client',
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);

            return $user;
        });
    }

    /**
     * Delete a user.
     */
    public function deleteUser(User $user): bool
    {
        return DB::transaction(function () use ($user) {
            // Can't delete yourself
            if ($user->id === Auth::id()) {
                throw new \Exception('You cannot delete your own account.');
            }

            // Handle user's data (properties, appointments, etc.)
            // This would depend on your business requirements
            // You might want to transfer ownership or soft delete

            return $user->delete();
        });
    }
}
