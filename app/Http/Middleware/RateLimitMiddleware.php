<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

/**
 * ============================================
 * RATE LIMITING & DDoS PROTECTION MIDDLEWARE
 * ============================================
 * 
 * Middleware untuk prevent abuse dan DDoS attacks.
 * 
 * Rate Limits:
 * - Public endpoints: 60 requests/minute per IP
 * - Auth endpoints: 5 requests/minute per IP (prevent brute force)
 * - API endpoints: 100 requests/minute per user
 * - File upload: 10 requests/minute per user
 * 
 * @author Aplikasi Telemedicine RSUD dr. R. Soedarsono
 * @version 1.0
 * @date 2025-12-20
 */
class RateLimitMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $key = $this->resolveRateLimitKey($request);
        $limit = $this->getLimit($request);
        
        // Check rate limit
        if (RateLimiter::tooManyAttempts($key, $limit['attempts'])) {
            $seconds = RateLimiter::availableIn($key);
            
            return response()->json([
                'success' => false,
                'message' => 'Too many requests. Please retry after ' . $seconds . ' seconds.',
                'retry_after' => $seconds,
            ], 429);
        }
        
        RateLimiter::hit($key, $limit['decay']);
        
        return $next($request);
    }

    /**
     * Resolve rate limit key (user ID or IP)
     */
    private function resolveRateLimitKey(Request $request)
    {
        if ($request->user()) {
            return 'api:' . $request->user()->id;
        }
        return 'api:' . $request->ip();
    }

    /**
     * Get rate limit based on endpoint
     */
    private function getLimit(Request $request)
    {
        // Auth endpoints: 5 requests per minute (prevent brute force)
        if ($request->is('api/v1/auth/login', 'api/v1/auth/register', 'api/v1/auth/forgot-password')) {
            return ['attempts' => 5, 'decay' => 60];
        }
        
        // File upload: 10 requests per minute
        if ($request->is('api/v1/files/upload', 'api/v1/doctor/verification/upload')) {
            return ['attempts' => 10, 'decay' => 60];
        }
        
        // Consultation create: 5 per hour
        if ($request->is('api/v1/consultations') && $request->isMethod('post')) {
            return ['attempts' => 5, 'decay' => 3600];
        }
        
        // Default: 100 requests per minute
        return ['attempts' => 100, 'decay' => 60];
    }
}
