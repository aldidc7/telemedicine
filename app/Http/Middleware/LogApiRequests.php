<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class LogApiRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        
        // Log incoming request
        Log::channel('api')->info('API Request Started', [
            'method' => $request->method(),
            'path' => $request->path(),
            'url' => $request->url(),
            'ip' => $request->ip(),
            'user_id' => Auth::id(),
            'headers' => $this->sanitizeHeaders($request->headers->all()),
        ]);

        // Get response
        $response = $next($request);
        
        // Calculate execution time
        $duration = (microtime(true) - $startTime) * 1000;

        // Log response
        Log::channel('api')->info('API Request Completed', [
            'method' => $request->method(),
            'path' => $request->path(),
            'status' => $response->status(),
            'duration_ms' => round($duration, 2),
            'user_id' => Auth::id(),
        ]);

        return $response;
    }

    /**
     * Sanitize headers to remove sensitive data
     */
    private function sanitizeHeaders(array $headers): array
    {
        $sensitive = ['authorization', 'cookie', 'x-api-key', 'x-auth-token'];
        $sanitized = [];

        foreach ($headers as $key => $value) {
            if (in_array(strtolower($key), $sensitive)) {
                $sanitized[$key] = '***REDACTED***';
            } else {
                $sanitized[$key] = is_array($value) ? implode(', ', $value) : $value;
            }
        }

        return $sanitized;
    }
}
