<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // API routes that don't need CSRF protection
        'api/*',
        'webhook/*',
        'stripe/*',
        'payment/*',
        
        // Add any other routes that should be excluded
        'sanctum/csrf-cookie',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     *
     * @throws \Illuminate\Session\TokenMismatchException
     */
    public function handle($request, Closure $next)
    {
        // Skip CSRF verification for API requests and excluded routes
        if ($this->inExceptArray($request) || 
            $request->expectsJson() || 
            $this->isApiRequest($request)) {
            return $next($request);
        }

        // Custom CSRF token validation for SPA applications
        if ($this->isSpaRequest($request)) {
            return $this->handleSpaRequest($request, $next);
        }

        return parent::handle($request, $next);
    }

    /**
     * Determine if the request is an API request.
     */
    protected function isApiRequest(Request $request): bool
    {
        return $request->is('api/*') || 
               $request->expectsJson() ||
               str_contains($request->header('Accept') ?? '', 'application/json');
    }

    /**
     * Determine if the request is from a Single Page Application.
     */
    protected function isSpaRequest(Request $request): bool
    {
        return $request->header('X-Requested-With') === 'XMLHttpRequest' &&
               $request->hasHeader('X-CSRF-TOKEN') &&
               !$this->isApiRequest($request);
    }

    /**
     * Handle CSRF verification for SPA requests.
     */
    protected function handleSpaRequest(Request $request, Closure $next)
    {
        $token = $request->header('X-CSRF-TOKEN');

        if (!$token || !hash_equals($request->session()->token(), $token)) {
            return response()->json([
                'message' => 'CSRF token mismatch.',
                'error' => 'The CSRF token is invalid or has expired.',
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }

    /**
     * Determine if the session and input CSRF tokens match.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function tokensMatch($request): bool
    {
        $token = $this->getTokenFromRequest($request);

        // For API requests with Bearer tokens, skip CSRF check
        if ($request->bearerToken() && $this->isApiRequest($request)) {
            return true;
        }

        return is_string($request->session()->token()) &&
               is_string($token) &&
               hash_equals($request->session()->token(), $token);
    }

    /**
     * Get the CSRF token from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function getTokenFromRequest($request): ?string
    {
        $token = $request->input('_token') ?: $request->header('X-CSRF-TOKEN');

        if (!$token && $header = $request->header('X-XSRF-TOKEN')) {
            $token = $this->encrypter->decrypt($header, static::serialized());
        }

        return $token;
    }

    /**
     * Add a new URI to the except array.
     */
    public function addExcept(array|string $uris): void
    {
        $uris = is_array($uris) ? $uris : func_get_args();

        $this->except = array_merge($this->except, $uris);
    }

    /**
     * Get the current except array.
     */
    public function getExcept(): array
    {
        return $this->except;
    }
}