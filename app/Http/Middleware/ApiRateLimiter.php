<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Cache\RateLimiter;

/**
 * ============================================
 * RATE LIMITING MIDDLEWARE
 * ============================================
 * 
 * Prevent abuse dengan rate limiting per endpoint
 */
class ApiRateLimiter
{
    protected $limiter;

    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = $this->getKey($request);
        
        // Different limits untuk different endpoints
        $limits = [
            'api:auth:login' => 5,      // 5 attempts per minute
            'api:auth:register' => 3,   // 3 per minute
            'api:pesan:store' => 60,    // 60 per minute
            'api:konsultasi:store' => 20, // 20 per minute
            'api:upload' => 10,         // 10 per minute
        ];

        $limit = $limits[$key] ?? 60;

        if ($this->limiter->tooManyAttempts($key, $limit)) {
            $retryAfter = $this->limiter->availableIn($key);
            
            return response()->json([
                'success' => false,
                'pesan' => 'Too many requests. Please try again later.',
                'retry_after' => $retryAfter,
            ], 429)->header('Retry-After', $retryAfter);
        }

        $this->limiter->hit($key, 60); // 60 seconds window

        return $next($request);
    }

    /**
     * Get rate limit key untuk request
     */
    protected function getKey(Request $request): string
    {
        // Use user ID jika authenticated
        if ($request->user()) {
            $userId = $request->user()->id;
        } else {
            $userId = $request->ip();
        }

        // Combine with endpoint
        $endpoint = $request->path();
        
        return "api_rate_{$userId}_{$endpoint}";
    }
}
