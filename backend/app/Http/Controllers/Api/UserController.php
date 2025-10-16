<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class UserController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private UserService $userService)
    {
        // Middleware is handled in routes
    }

    /**
     * Display a listing of users (admin only).
     */
    public function index(Request $request): AnonymousResourceCollection|JsonResponse
    {
        try {
            $this->authorize('viewAny', User::class);

            $users = $this->userService->getAllUsers($request->all());

            return UserResource::collection($users);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch users',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): JsonResponse
    {
        try {
            $this->authorize('view', $user);

            $user->load(['properties.images']);

            return response()->json([
                'user' => new UserResource($user),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update user profile.
     */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        try {
            $user = $this->userService->updateProfile(
                $request->user(), 
                $request->validated()
            );

            return response()->json([
                'message' => 'Profile updated successfully',
                'user' => new UserResource($user),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update profile',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update user avatar.
     */
    public function updateAvatar(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'avatar' => 'required|image|max:2048', // 2MB max
            ]);

            $user = $this->userService->updateAvatar(
                $request->user(), 
                $request->file('avatar')
            );

            return response()->json([
                'message' => 'Avatar updated successfully',
                'user' => new UserResource($user),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update avatar',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete user avatar.
     */
    public function deleteAvatar(Request $request): JsonResponse
    {
        try {
            $user = $this->userService->deleteAvatar($request->user());

            return response()->json([
                'message' => 'Avatar removed successfully',
                'user' => new UserResource($user),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to remove avatar',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update user role (admin only).
     */
    public function updateRole(User $user, Request $request): JsonResponse
    {
        try {
            $this->authorize('updateRole', $user);

            $request->validate([
                'role' => 'required|in:admin,agent,client',
            ]);

            $updatedUser = $this->userService->updateRole($user, $request->role);

            return response()->json([
                'message' => 'User role updated successfully',
                'user' => new UserResource($updatedUser),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update user role',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle user active status (admin only).
     */
    public function toggleActive(User $user): JsonResponse
    {
        try {
            $this->authorize('update', $user);

            $updatedUser = $this->userService->toggleActive($user);

            return response()->json([
                'message' => 'User status updated successfully',
                'user' => new UserResource($updatedUser),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update user status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user statistics.
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $stats = $this->userService->getStatistics($user);

            return response()->json([
                'statistics' => $stats,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch user statistics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get agents list.
     */
    public function agents(): AnonymousResourceCollection|JsonResponse
    {
        try {
            $agents = $this->userService->getAgents();

            return UserResource::collection($agents);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch agents',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Change user password.
     */
    public function changePassword(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'current_password' => 'required|current_password',
                'new_password' => 'required|min:8|confirmed',
            ]);

            $this->userService->changePassword(
                $request->user(), 
                $request->new_password
            );

            return response()->json([
                'message' => 'Password changed successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to change password',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}