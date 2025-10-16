<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                // For API requests, return error if already authenticated
                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json([
                        'message' => 'Already authenticated',
                        'error' => 'You are already logged in. Please log out first.',
                    ], 400);
                }

                // For web requests, redirect based on user role
                $user = Auth::guard($guard)->user();
                
                if ($user->role === 'admin') {
                    return redirect('/admin/dashboard');
                } elseif ($user->role === 'agent') {
                    return redirect('/agent/dashboard');
                } else {
                    return redirect(RouteServiceProvider::HOME);
                }
            }
        }

        return $next($request);
    }

    /**
     * Get the redirect path based on user role.
     */
    protected function getRedirectPath($user): string
    {
        return match($user->role) {
            'admin' => '/admin/dashboard',
            'agent' => '/agent/dashboard',
            default => RouteServiceProvider::HOME,
        };
    }
}