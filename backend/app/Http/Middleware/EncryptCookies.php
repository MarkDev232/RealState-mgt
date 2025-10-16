<?php

namespace App\Http\Middleware;

use Illuminate\Cookie\Middleware\EncryptCookies as Middleware;

class EncryptCookies extends Middleware
{
    /**
     * The names of the cookies that should not be encrypted.
     *
     * @var array<int, string>
     */
    protected $except = [
        // Add any cookies that should not be encrypted
        // For example, if you have a cookie that needs to be read by JavaScript
    ];

    /**
     * Determine if the cookie should be encrypted.
     *
     * @param  string  $name
     * @return bool
     */
    public function isDisabled($name)
    {
        // Check if the cookie is in the except list
        if (in_array($name, $this->except)) {
            return true;
        }

        // Additional logic for dynamic cookie exclusion
        // For example, exclude cookies from specific domains or paths
        if (str_starts_with($name, 'analytics_')) {
            return true;
        }

        return false;
    }
}