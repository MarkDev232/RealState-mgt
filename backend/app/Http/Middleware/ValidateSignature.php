<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Routing\Middleware\ValidateSignature as Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateSignature extends Middleware
{
    /**
     * The names of the query string parameters that should be ignored.
     *
     * @var array<int, string>
     */
    protected $except = [
        // Add any parameters that should be excluded from signature validation
        'fbclid',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_term',
        'utm_content',
        'gclid',
        '_ga',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  mixed  ...$args
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    public function handle($request, Closure $next, ...$args): Response
    {
        // Check if we should validate the signature
        if (!$this->shouldValidateSignature($request)) {
            return $next($request);
        }

        // Clean parameters
        $cleanedParams = $this->cleanParameters($request);
        $cleanedRequest = $request->duplicate($cleanedParams);

        try {
            // Use parent's handle method with cleaned request
            return parent::handle($cleanedRequest, $next, ...$args);
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Invalid or expired URL signature.',
                    'error' => 'The provided URL signature is invalid or has expired.',
                ], 401);
            }
            abort(401, 'Invalid or expired URL signature.');
        }
    }

    /**
     * Determine if the signature should be validated for the current request.
     */
    protected function shouldValidateSignature(Request $request): bool
    {
        // Skip signature validation for certain routes
        $route = $request->route();

        if ($route && in_array($route->getName(), $this->getExcludedRoutes())) {
            return false;
        }

        // Skip if no signature parameter is present
        if (!$request->has('signature')) {
            return false;
        }

        return true;
    }

    /**
     * Get the list of routes that should be excluded from signature validation.
     */
    protected function getExcludedRoutes(): array
    {
        return [
            'verification.verify',
            'password.reset',
            // Add other routes that should not require signature validation
        ];
    }

    /**
     * Clean the request parameters by removing excluded parameters.
     */
    protected function cleanParameters(Request $request): array
    {
        $params = $request->query();

        foreach ($this->except as $param) {
            unset($params[$param]);
        }

        return $params;
    }
}