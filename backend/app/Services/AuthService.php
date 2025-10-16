<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Register a new user.
     */
    public function register(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => $data['role'] ?? 'client',
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
            ]);

            return $user;
        });
    }

    /**
     * Login user.
     */
    public function login(array $credentials): User
    {
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Your account has been deactivated. Please contact support.'],
            ]);
        }

        return $user;
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
     * Send password reset link.
     */
    public function sendPasswordResetLink(string $email): bool
    {
        // Implementation for sending password reset link
        // This would integrate with Laravel's password reset system
        return true;
    }

    /**
     * Reset user password.
     */
    public function resetPassword(array $data): bool
    {
        // Implementation for password reset
        // This would integrate with Laravel's password reset system
        return true;
    }

    /**
     * Verify user email.
     */
    public function verifyEmail(User $user): bool
    {
        return $user->update([
            'email_verified_at' => now(),
        ]);
    }

    /**
     * Send email verification notification.
     */
    public function sendEmailVerificationNotification(User $user): void
    {
        $user->sendEmailVerificationNotification();
    }
}