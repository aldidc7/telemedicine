<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\RateLimitingService;
use App\Services\QueryMonitoringService;

/**
 * Performance Middleware
 * 
 * Handles:
 * - Rate limiting
 * - Query monitoring
 * - Response optimization
 * - Performance headers
 */
class PerformanceMiddleware
{
    /**
     * Handle the request
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Start request timer
        $startTime = microtime(true);

        // Check rate limiting
        $identifier = $this->getIdentifier($request);
        $user = auth()->user();
        $tier = RateLimitingService::getUserTier($user);
        $limitData = RateLimitingService::checkLimit($identifier, $tier);

        // If rate limited, return 429
        if (!$limitData['allowed']) {
            return response()->json([
                'success' => false,
                'message' => 'Rate limit exceeded',
            ], 429, RateLimitingService::getHeaders($limitData));
        }

        // Reset query monitoring stats
        QueryMonitoringService::reset();

        // Get response
        $response = $next($request);

        // Calculate request time
        $requestTime = round((microtime(true) - $startTime) * 1000, 2); // in ms

        // Add rate limit headers
        foreach (RateLimitingService::getHeaders($limitData) as $header => $value) {
            $response->header($header, $value);
        }

        // Add performance headers
        $response->header('X-Request-Time', $requestTime . 'ms');
        $response->header('X-Total-Queries', QueryMonitoringService::getStats()['total_queries']);
        $response->header('X-Query-Time', round(QueryMonitoringService::getStats()['total_time_ms'], 2) . 'ms');
        $response->header('X-API-Version', '1.0');

        // Log slow requests (> 1 second)
        if ($requestTime > 1000) {
            \Log::warning('Slow request detected', [
                'path' => $request->path(),
                'method' => $request->method(),
                'time_ms' => $requestTime,
                'queries' => QueryMonitoringService::getStats()['total_queries'],
            ]);
        }

        return $response;
    }

    /**
     * Get unique identifier for rate limiting
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    private function getIdentifier(Request $request): string
    {
        // Use user ID if authenticated, otherwise use IP
        if (auth()->check()) {
            return 'user:' . auth()->id();
        }

        return 'ip:' . $request->ip();
    }
}
