<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance as Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventRequestsDuringMaintenance extends Middleware
{
    /**
     * The URIs that should be reachable while maintenance mode is enabled.
     *
     * @var array<int, string>
     */
    protected $except = [
        // Add routes that should be accessible during maintenance
        '/api/health',
        '/api/status',
        '/admin/*', // Allow admin access during maintenance
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function handle($request, Closure $next)
    {
        if ($this->app->isDownForMaintenance()) {
            // Check if the request is for an excepted URI
            foreach ($this->except as $except) {
                if ($except !== '/') {
                    $except = trim($except, '/');
                }

                if ($request->is($except)) {
                    return $next($request);
                }
            }

            // For API requests, return JSON response
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Service Unavailable',
                    'error' => 'The application is currently undergoing maintenance. Please try again later.',
                    'retry_after' => 300, // 5 minutes
                ], Response::HTTP_SERVICE_UNAVAILABLE);
            }

            // For web requests, use the default maintenance mode response
            return parent::handle($request, $next);
        }

        return $next($request);
    }
}