<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Unauthenticated.',
                    'error' => 'You must be logged in to access this resource.',
                ], Response::HTTP_UNAUTHORIZED);
            }

            return redirect()->route('login');
        }

        $user = Auth::user();

        // Check if user has any of the required roles
        if (!in_array($user->role, $roles)) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Access Denied.',
                    'error' => 'You do not have permission to access this resource.',
                    'required_roles' => $roles,
                    'current_role' => $user->role,
                ], Response::HTTP_FORBIDDEN);
            }

            // For web requests, redirect based on user role
            return $this->redirectToDefaultRoute($user);
        }

        // Additional permission checks can be added here
        if (!$this->checkAdditionalPermissions($user, $request)) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Insufficient Permissions.',
                    'error' => 'You do not have sufficient permissions to perform this action.',
                ], Response::HTTP_FORBIDDEN);
            }

            abort(Response::HTTP_FORBIDDEN, 'Insufficient permissions.');
        }

        return $next($request);
    }

    /**
     * Redirect user to their default route based on role.
     */
    protected function redirectToDefaultRoute($user): Response
    {
        return match ($user->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'agent' => redirect()->route('agent.dashboard'),
            'client' => redirect()->route('home'),
            default => redirect()->route('login'),
        };
    }

    /**
     * Check additional permissions beyond role-based access.
     */
    protected function checkAdditionalPermissions($user, Request $request): bool
    {
        // Check for multiple HTTP methods
        if ($user->role === 'agent' && in_array($request->method(), ['PUT', 'PATCH', 'DELETE'])) {
            return $this->checkAgentOwnership($user, $request);
        }

        // Example: Check if user is accessing their own data
        if ($request->route('user') && $request->route('user')->id !== $user->id) {
            return $user->role === 'admin';
        }

        return true;
    }

    /**
     * Check if agent owns the resource they're trying to modify.
     */
    protected function checkAgentOwnership($user, Request $request): bool
    {
        // Check for property ownership
        if ($request->route('property')) {
            return $request->route('property')->agent_id === $user->id;
        }

        // Check for appointment ownership (as agent)
        if ($request->route('appointment')) {
            return $request->route('appointment')->agent_id === $user->id;
        }

        // Check for inquiry access (agent must own the property)
        if ($request->route('inquiry')) {
            return $request->route('inquiry')->property->agent_id === $user->id;
        }

        return true;
    }

    /**
     * Get the required roles for the current route.
     */
    public static function getRequiredRoles(): array
    {
        $route = request()->route();

        if ($route && isset($route->action['roles'])) {
            return (array) $route->action['roles'];
        }

        return [];
    }

    /**
     * Check if the current user has any of the given roles.
     */
    public static function hasAnyRole(array $roles): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        return in_array($user->role, $roles);
    }

    /**
     * Check if the current user has all of the given roles.
     */
    public static function hasAllRoles(array $roles): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        foreach ($roles as $role) {
            if ($user->role !== $role) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if the current user has a specific role.
     */
    public static function hasRole(string $role): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        return $user->role === $role;
    }
}
