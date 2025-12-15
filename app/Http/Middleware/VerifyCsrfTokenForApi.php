<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyCsrfTokenForApi
{
    /**
     * Endpoints yang EXEMPT dari CSRF verification
     * (sudah have authentication via token)
     */
    protected $except = [
        'api/*', // API routes sudah have Sanctum token
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // For API requests dengan Sanctum, CSRF tidak needed (token auth sudah cukup)
        // Tapi tetap add CSRF token di response untuk client yg ingin implement itu

        $response = $next($request);

        // Add CSRF token ke setiap response (untuk UI yg perlu)
        if ($request->expectsJson()) {
            $csrfToken = csrf_token();
            $response->headers->set('X-CSRF-Token', $csrfToken);
        }

        return $response;
    }
}
