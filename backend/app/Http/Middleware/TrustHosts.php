<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustHosts as Middleware;

class TrustHosts extends Middleware
{
    /**
     * Get the host patterns that should be trusted.
     *
     * @return array<int, string|null>
     */
    public function hosts(): array
    {
        return [
            $this->allSubdomainsOfApplicationUrl(),
            // Add specific hosts that should be trusted
            $this->getApplicationHost(),
            // Trust localhost and common development domains
            'localhost',
            '127.0.0.1',
            '::1',
        ];
    }

    /**
     * Get the application host from the configuration.
     */
    protected function getApplicationHost(): ?string
    {
        $url = config('app.url');
        
        if ($url) {
            $parsed = parse_url($url);
            return $parsed['host'] ?? null;
        }

        return null;
    }

    /**
     * Get the application domain without subdomains.
     */
    protected function getApplicationDomain(): ?string
    {
        $host = $this->getApplicationHost();
        
        if (!$host) {
            return null;
        }

        // Remove subdomains (simple approach)
        $parts = explode('.', $host);
        
        if (count($parts) > 2) {
            return $parts[count($parts) - 2] . '.' . $parts[count($parts) - 1];
        }

        return $host;
    }

    /**
     * Determine if the given host is trusted.
     *
     * @param  string  $host
     * @return bool
     */
    public function shouldTrustHost($host): bool
    {
        foreach ($this->hosts() as $trustedHost) {
            if ($trustedHost === null) {
                continue;
            }

            if ($trustedHost === $host) {
                return true;
            }

            // Check for subdomain patterns
            if (str_starts_with($trustedHost, '*') && $this->isMatchingWildcardHost($trustedHost, $host)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a host matches a wildcard pattern.
     */
    protected function isMatchingWildcardHost(string $pattern, string $host): bool
    {
        $pattern = str_replace('\*', '[^.]*', preg_quote($pattern, '/'));
        return (bool) preg_match('/^' . $pattern . '$/i', $host);
    }
}