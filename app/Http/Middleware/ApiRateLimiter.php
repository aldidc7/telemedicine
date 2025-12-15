<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

/**
 * ============================================
 * RATE LIMITING MIDDLEWARE
 * ============================================
 * 
 * Prevent API abuse dengan rate limiting per user/IP
 * 
 * Rate Limits:
 * - Auth (login/register): 5 requests per minute per IP
 * - Message/Chat: 60 requests per minute per user
 * - Konsultasi: 20 requests per minute per user
 * - File Upload: 10 requests per minute per user
 * - Search/Filter: 30 requests per minute per user
 * - General API: 60 requests per minute per user
 */
class ApiRateLimiter
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip rate limiting untuk health check endpoint
        if ($request->path() === 'v1/health') {
            return $next($request);
        }

        $key = $this->getKey($request);
        $limit = $this->getLimit($request);
        $decay = 60; // 60 seconds

        // Check if too many attempts
        if (RateLimiter::tooManyAttempts($key, $limit)) {
            $retryAfter = RateLimiter::availableIn($key);
            
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 429,
                    'title' => 'Too Many Requests',
                    'message' => 'Rate limit exceeded. Please try again in ' . $retryAfter . ' seconds.',
                ],
                'retry_after' => $retryAfter,
            ], 429)
            ->header('Retry-After', $retryAfter)
            ->header('X-RateLimit-Limit', $limit)
            ->header('X-RateLimit-Remaining', 0)
            ->header('X-RateLimit-Reset', now()->addSeconds($retryAfter)->timestamp);
        }

        // Hit the rate limiter
        RateLimiter::hit($key, $decay);

        // Get remaining attempts
        $remaining = max(0, $limit - RateLimiter::attempts($key));

        // Add rate limit headers to response
        return $next($request)
            ->header('X-RateLimit-Limit', $limit)
            ->header('X-RateLimit-Remaining', $remaining)
            ->header('X-RateLimit-Reset', now()->addSeconds($decay)->timestamp);
    }

    /**
     * Get unique rate limit key untuk request
     * Combines user ID (if authenticated) or IP address with endpoint group
     */
    private function getKey(Request $request): string
    {
        $identifier = $request->user() 
            ? 'user:' . $request->user()->id 
            : 'ip:' . $request->ip();

        $endpoint = $this->getEndpointGroup($request);

        return "api_rate:{$identifier}:{$endpoint}";
    }

    /**
     * Determine rate limit berdasarkan endpoint
     * 
     * Different endpoints memiliki batas yang berbeda
     */
    private function getLimit(Request $request): int
    {
        $path = $request->path();

        // Auth endpoints - sangat ketat (brute force protection)
        if ($this->isAuthEndpoint($path)) {
            return 5; // 5 attempts per minute
        }

        // File upload - ketat untuk mencegah abuse storage
        if ($this->isUploadEndpoint($path)) {
            return 10; // 10 attempts per minute
        }

        // Konsultasi - moderate (dapat expensive operation)
        if ($this->isKonsultasiEndpoint($path)) {
            return 20; // 20 attempts per minute
        }

        // Search/filter - moderate untuk prevent DoS
        if ($this->isSearchEndpoint($path)) {
            return 30; // 30 attempts per minute
        }

        // Admin endpoints - generous
        if ($this->isAdminEndpoint($path)) {
            return 100; // 100 attempts per minute
        }

        // Default untuk general API
        return 60; // 60 attempts per minute
    }

    /**
     * Kelompokkan endpoint untuk tracking yang lebih baik
     */
    private function getEndpointGroup(Request $request): string
    {
        $path = $request->path();

        if ($this->isAuthEndpoint($path)) {
            return 'auth';
        }

        if ($this->isUploadEndpoint($path)) {
            return 'upload';
        }

        if ($this->isKonsultasiEndpoint($path)) {
            return 'konsultasi';
        }

        if ($this->isSearchEndpoint($path)) {
            return 'search';
        }

        if ($this->isAdminEndpoint($path)) {
            return 'admin';
        }

        return 'general';
    }

    /**
     * Check if request adalah auth endpoint
     */
    private function isAuthEndpoint(string $path): bool
    {
        return str_contains($path, 'auth/login')
            || str_contains($path, 'auth/register')
            || str_contains($path, 'auth/forgot-password')
            || str_contains($path, 'auth/reset-password');
    }

    /**
     * Check if request adalah upload endpoint
     */
    private function isUploadEndpoint(string $path): bool
    {
        return str_contains($path, 'upload')
            || str_contains($path, 'photo')
            || str_contains($path, 'image')
            || str_contains($path, 'file');
    }

    /**
     * Check if request adalah konsultasi endpoint
     */
    private function isKonsultasiEndpoint(string $path): bool
    {
        return str_contains($path, 'konsultasi')
            || str_contains($path, 'appointments')
            || str_contains($path, 'appointment');
    }

    /**
     * Check if request adalah search endpoint
     */
    private function isSearchEndpoint(string $path): bool
    {
        return str_contains($path, 'search')
            || str_contains($path, 'filter')
            || str_contains($path, 'dokter');
    }

    /**
     * Check if request adalah admin endpoint
     */
    private function isAdminEndpoint(string $path): bool
    {
        return str_contains($path, 'admin');
    }
}
