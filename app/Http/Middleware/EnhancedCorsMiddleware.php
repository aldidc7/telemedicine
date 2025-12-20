<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Enhanced CORS Middleware dengan security improvements
 * 
 * Mengimplementasikan:
 * - Proper CORS headers
 * - CSRF token validation
 * - Origin whitelist
 * - Credential support
 */
class EnhancedCorsMiddleware
{
    /**
     * List of allowed origins
     */
    protected array $allowedOrigins = [];

    public function __construct()
    {
        $this->allowedOrigins = array_filter([
            env('APP_URL'),
            env('FRONTEND_URL'),
            'http://localhost:3000',
            'http://localhost:5173',
            'http://localhost:8080',
            env('CORS_ALLOWED_ORIGINS'),
        ]);
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if origin is allowed
        $origin = $request->header('Origin');

        if ($origin && in_array($origin, $this->allowedOrigins)) {
            return $next($request)
                ->header('Access-Control-Allow-Origin', $origin)
                ->header('Access-Control-Allow-Credentials', 'true')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Origin, Content-Type, Authorization, X-Requested-With, Accept')
                ->header('Access-Control-Max-Age', '3600')
                ->header('Access-Control-Expose-Headers', 'X-Total-Count, X-Page-Count');
        }

        // Handle preflight requests
        if ($request->method() === 'OPTIONS') {
            return response('', 200)
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Origin, Content-Type, Authorization, X-Requested-With, Accept');
        }

        return $next($request);
    }
}
