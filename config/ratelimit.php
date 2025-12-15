<?php

/**
 * Rate Limiting Configuration
 * 
 * Defines rate limits untuk berbagai endpoint categories
 * untuk mencegah API abuse dan DoS attacks
 */

return [
    /**
     * Endpoint-specific rate limits (requests per minute)
     * 
     * Format: 'endpoint_pattern' => [
     *     'limit' => number of requests,
     *     'decay' => seconds,
     *     'description' => 'Purpose of this limit'
     * ]
     */
    'limits' => [
        // Authentication endpoints - very strict
        'auth' => [
            'limit' => 5,
            'decay' => 60,
            'description' => 'Login/Register attempts per minute (brute force protection)',
        ],

        // File upload endpoints - strict
        'upload' => [
            'limit' => 10,
            'decay' => 60,
            'description' => 'File uploads per minute (storage protection)',
        ],

        // Konsultasi/Appointment endpoints - moderate
        'konsultasi' => [
            'limit' => 20,
            'decay' => 60,
            'description' => 'Appointment requests per minute (DB intensive operation)',
        ],

        // Search/Filter endpoints - moderate
        'search' => [
            'limit' => 30,
            'decay' => 60,
            'description' => 'Search/Filter requests per minute (query intensive)',
        ],

        // Admin endpoints - generous
        'admin' => [
            'limit' => 100,
            'decay' => 60,
            'description' => 'Admin panel requests per minute',
        ],

        // General API - default
        'general' => [
            'limit' => 60,
            'decay' => 60,
            'description' => 'General API requests per minute',
        ],
    ],

    /**
     * Endpoints yang di-exclude dari rate limiting
     * (e.g., health checks, public endpoints)
     */
    'excluded_endpoints' => [
        'v1/health',
        'up', // Laravel health check
    ],

    /**
     * Cache driver untuk rate limiting
     * 
     * Recommended: redis (fastest)
     * Alternative: database, file
     */
    'cache_driver' => env('RATE_LIMIT_CACHE', 'database'),

    /**
     * Enable/disable rate limiting globally
     */
    'enabled' => env('API_RATE_LIMITING_ENABLED', true),

    /**
     * Response format untuk rate limit exceeded
     */
    'response_format' => [
        'status_code' => 429,
        'include_reset_time' => true,
        'include_retry_after' => true,
        'include_limit_headers' => true, // X-RateLimit-Limit, X-RateLimit-Remaining, X-RateLimit-Reset
    ],

    /**
     * Whitelist IPs yang exempt dari rate limiting
     * Contoh untuk internal monitoring, CI/CD, dll
     */
    'whitelist_ips' => explode(',', env('RATE_LIMIT_WHITELIST_IPS', '')),

    /**
     * Rate limit multipliers untuk different user types
     * (dapat dikustom per role)
     */
    'user_multipliers' => [
        'admin' => 2.0,    // Admin dapat 2x lebih banyak requests
        'dokter' => 1.5,   // Dokter dapat 1.5x lebih banyak requests
        'pasien' => 1.0,   // Pasien mendapat default limit
    ],
];
