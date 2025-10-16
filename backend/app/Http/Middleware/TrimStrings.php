<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\TrimStrings as Middleware;

class TrimStrings extends Middleware
{
    /**
     * The names of the attributes that should not be trimmed.
     *
     * @var array<int, string>
     */
    protected $except = [
        'current_password',
        'password',
        'password_confirmation',
        // Add any fields that should not be trimmed
        'html_content',
        'json_data',
        'raw_content',
    ];

    /**
     * Transform the given value.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return mixed
     */
    protected function transform($key, $value)
    {
        // Don't trim fields in the except list
        if (in_array($key, $this->except, true)) {
            return $value;
        }

        // Don't trim arrays or objects
        if (! is_string($value)) {
            return $value;
        }

        // Custom trimming logic
        $value = $this->deepTrim($value);

        return $value;
    }

    /**
     * Perform deep trimming on strings, including multi-dimensional arrays.
     */
    protected function deepTrim($value)
    {
        if (is_array($value)) {
            return array_map([$this, 'deepTrim'], $value);
        }

        if (! is_string($value)) {
            return $value;
        }

        // Trim the string and convert multiple spaces to single space
        $value = trim($value);
        $value = preg_replace('/\s+/', ' ', $value);

        return $value;
    }

    
}