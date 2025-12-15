<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AddSecurityHeaders
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        /**
         * Content Security Policy (CSP)
         * Prevents XSS attacks by restricting resource loading
         */
        $response->headers->set(
            'Content-Security-Policy',
            "default-src 'self'; " .
            "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; " .
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; " .
            "img-src 'self' data: https:; " .
            "font-src 'self' https://fonts.gstatic.com; " .
            "connect-src 'self' https://api.pusher.com https://sockjs.pusher.com; " .
            "frame-ancestors 'none'; " .
            "base-uri 'self'; " .
            "form-action 'self'"
        );

        /**
         * X-Content-Type-Options: nosniff
         * Prevents browser from MIME-sniffing
         */
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        /**
         * X-Frame-Options: DENY
         * Prevents clickjacking attacks
         */
        $response->headers->set('X-Frame-Options', 'DENY');

        /**
         * X-XSS-Protection: 1; mode=block
         * Enables XSS protection in older browsers
         */
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        /**
         * Referrer-Policy: strict-origin-when-cross-origin
         * Controls how much referrer information is shared
         */
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        /**
         * Permissions-Policy (formerly Feature-Policy)
         * Controls which features can be used in the browser
         */
        $response->headers->set(
            'Permissions-Policy',
            'geolocation=(), ' .
            'microphone=(), ' .
            'camera=(), ' .
            'payment=(), ' .
            'usb=(), ' .
            'magnetometer=(), ' .
            'gyroscope=(), ' .
            'accelerometer=()'
        );

        /**
         * Strict-Transport-Security (HSTS)
         * Forces HTTPS connection (enable in production)
         */
        if ($request->isSecure() || config('app.env') === 'production') {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains; preload'
            );
        }

        /**
         * Remove sensitive headers
         */
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

        return $response;
    }
}
