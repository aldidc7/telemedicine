<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure CORS settings for your application. This
    | configuration is used by the CORS middleware to determine which
    | cross-origin requests should be allowed.
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    /**
     * Allowed origins - RESTRICTED FOR SECURITY
     * Only specific domains allowed to access the API
     */
    'allowed_origins' => [
        // Development
        'http://localhost:3000',
        'http://localhost:5173',
        'http://localhost:8000',
        'http://127.0.0.1:3000',
        'http://127.0.0.1:5173',
        'http://127.0.0.1:8000',

        // Production (update dengan domain actual)
        // 'https://telemedicine.com',
        // 'https://app.telemedicine.com',
        // 'https://admin.telemedicine.com',
    ],

    'allowed_origins_patterns' => [
        // Uncomment jika ingin allow subdomains
        // '#^https:\/\/(.+\.)?telemedicine\.com$#',
    ],

    'allowed_methods' => ['*'],

    'allowed_headers' => ['*'],

    'exposed_headers' => [
        'Authorization',
        'Content-Type',
        'X-Total-Count',
        'X-Per-Page',
        'X-Current-Page',
        'X-Total-Pages',
        'X-RateLimit-Limit',
        'X-RateLimit-Remaining',
        'X-RateLimit-Reset',
    ],

    'max_age' => 0,

    'supports_credentials' => true,

];
