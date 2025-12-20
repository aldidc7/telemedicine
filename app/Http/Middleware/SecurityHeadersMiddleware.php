<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * ============================================
 * SECURITY HEADERS MIDDLEWARE
 * ============================================
 * 
 * Add security headers ke semua HTTP responses.
 * 
 * Headers:
 * - X-Content-Type-Options: prevent MIME sniffing
 * - X-Frame-Options: prevent clickjacking
 * - X-XSS-Protection: prevent XSS attacks
 * - Strict-Transport-Security: enforce HTTPS
 * - Content-Security-Policy: prevent inline scripts
 * 
 * @author Aplikasi Telemedicine RSUD dr. R. Soedarsono
 * @version 1.0
 * @date 2025-12-20
 */
class SecurityHeadersMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Prevent MIME type sniffing
        $response->header('X-Content-Type-Options', 'nosniff');

        // Prevent clickjacking
        $response->header('X-Frame-Options', 'DENY');

        // XSS protection
        $response->header('X-XSS-Protection', '1; mode=block');

        // Enforce HTTPS (if not in development)
        if (!app()->isLocal()) {
            $response->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // Content Security Policy
        $response->header('Content-Security-Policy', 
            "default-src 'self'; " .
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net; " .
            "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; " .
            "img-src 'self' data: https:; " .
            "font-src 'self' data: https://cdn.jsdelivr.net; " .
            "connect-src 'self' https:; " .
            "frame-ancestors 'none';"
        );

        // Referrer Policy
        $response->header('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions Policy (Feature Policy)
        $response->header('Permissions-Policy', 
            'geolocation=(), microphone=(), camera=()'
        );

        return $response;
    }
}
