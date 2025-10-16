<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * @var array<int, string>|string|null
     */
    protected $proxies = [
        // List of trusted proxies
        // You can specify IP addresses or use '*' to trust all proxies
        // '192.168.1.1',
        // '192.168.1.2',
    ];

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers = 
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;

    /**
     * Handle the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // For cloud environments, trust all proxies
        if ($this->isCloudEnvironment()) {
            $this->proxies = '*';
        }

        // For local development, trust all proxies
        if (app()->environment('local')) {
            $this->proxies = '*';
        }

        $request::setTrustedProxies($this->proxies, $this->getTrustedHeaderNames());

        return $next($request);
    }

    /**
     * Check if the application is running in a cloud environment.
     */
    protected function isCloudEnvironment(): bool
    {
        return in_array(env('APP_ENV'), ['production', 'staging']) ||
               !empty(env('CLOUD_PROVIDER')) ||
               !empty($_SERVER['HTTP_X_FORWARDED_FOR']);
    }

    /**
     * Get the trusted header names based on the headers property.
     */
    protected function getTrustedHeaderNames(): int
    {
        $headers = 0;

        if ($this->headers & SymfonyRequest::HEADER_X_FORWARDED_FOR) {
            $headers |= SymfonyRequest::HEADER_X_FORWARDED_FOR;
        }

        if ($this->headers & SymfonyRequest::HEADER_X_FORWARDED_HOST) {
            $headers |= SymfonyRequest::HEADER_X_FORWARDED_HOST;
        }

        if ($this->headers & SymfonyRequest::HEADER_X_FORWARDED_PORT) {
            $headers |= SymfonyRequest::HEADER_X_FORWARDED_PORT;
        }

        if ($this->headers & SymfonyRequest::HEADER_X_FORWARDED_PROTO) {
            $headers |= SymfonyRequest::HEADER_X_FORWARDED_PROTO;
        }

        if ($this->headers & SymfonyRequest::HEADER_X_FORWARDED_AWS_ELB) {
            $headers |= SymfonyRequest::HEADER_X_FORWARDED_AWS_ELB;
        }

        return $headers;
    }

    /**
     * Get the current trusted proxies.
     */
    public function getProxies(): array|string|null
    {
        return $this->proxies;
    }

    /**
     * Set the trusted proxies.
     */
    public function setProxies(array|string|null $proxies): void
    {
        $this->proxies = $proxies;
    }
}