<?php

namespace App\Services;

use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;

/**
 * Rate Limiter Service
 * 
 * Service untuk manage rate limiting dengan lebih flexible
 * Mendukung custom limits per user, per endpoint, dll
 */
class RateLimiterService
{
    /**
     * Check if request exceeds rate limit
     */
    public static function isLimited(Request $request, int $limit = 60, int $decay = 60): bool
    {
        $key = self::getKey($request);
        return RateLimiter::tooManyAttempts($key, $limit, $decay);
    }

    /**
     * Get remaining attempts untuk request
     */
    public static function remaining(Request $request, int $limit = 60): int
    {
        $key = self::getKey($request);
        $attempts = RateLimiter::attempts($key);
        return max(0, $limit - $attempts);
    }

    /**
     * Get seconds until retry
     */
    public static function retryAfter(Request $request): int
    {
        $key = self::getKey($request);
        return RateLimiter::availableIn($key);
    }

    /**
     * Hit rate limiter (record attempt)
     */
    public static function hit(Request $request, int $decay = 60): void
    {
        $key = self::getKey($request);
        RateLimiter::hit($key, $decay);
    }

    /**
     * Clear rate limiter untuk request (untuk testing, admin override, dll)
     */
    public static function clear(Request $request): void
    {
        $key = self::getKey($request);
        RateLimiter::clear($key);
    }

    /**
     * Get total attempts untuk request
     */
    public static function attempts(Request $request): int
    {
        $key = self::getKey($request);
        return RateLimiter::attempts($key);
    }

    /**
     * Get reset timestamp
     */
    public static function resetAt(Request $request, int $decay = 60): int
    {
        return now()->addSeconds($decay)->timestamp;
    }

    /**
     * Generate unique key untuk rate limiting
     * 
     * Kombinasi dari:
     * - User ID (jika authenticated) atau IP address
     * - Endpoint group (auth, upload, konsultasi, search, admin, general)
     */
    private static function getKey(Request $request): string
    {
        $identifier = $request->user() 
            ? 'user:' . $request->user()->id 
            : 'ip:' . $request->ip();

        $endpoint = self::getEndpointGroup($request->path());

        return "api_rate:{$identifier}:{$endpoint}";
    }

    /**
     * Get endpoint group dari path
     */
    private static function getEndpointGroup(string $path): string
    {
        if (self::match($path, ['auth/login', 'auth/register', 'auth/forgot-password', 'auth/reset-password'])) {
            return 'auth';
        }

        if (self::match($path, ['upload', 'photo', 'image', 'file'])) {
            return 'upload';
        }

        if (self::match($path, ['konsultasi', 'appointments', 'appointment'])) {
            return 'konsultasi';
        }

        if (self::match($path, ['search', 'filter', 'dokter'])) {
            return 'search';
        }

        if (self::match($path, ['admin'])) {
            return 'admin';
        }

        return 'general';
    }

    /**
     * Check if path matches any pattern
     */
    private static function match(string $path, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if (str_contains($path, $pattern)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get limit multiplier berdasarkan user role
     * Admin dapat 2x limit, Dokter dapat 1.5x, Pasien dapat 1x
     */
    public static function getMultiplier(Request $request): float
    {
        if (!$request->user()) {
            return 1.0;
        }

        $multipliers = config('ratelimit.user_multipliers', [
            'admin' => 2.0,
            'dokter' => 1.5,
            'pasien' => 1.0,
        ]);

        $userType = $request->user()->type ?? 'pasien';
        return $multipliers[$userType] ?? 1.0;
    }

    /**
     * Get adjusted limit berdasarkan user role
     */
    public static function getAdjustedLimit(Request $request, int $baseLimit): int
    {
        $multiplier = self::getMultiplier($request);
        return (int)($baseLimit * $multiplier);
    }

    /**
     * Get rate limit info untuk monitoring/debugging
     */
    public static function getInfo(Request $request, int $limit = 60, int $decay = 60): array
    {
        return [
            'key' => self::getKey($request),
            'attempts' => self::attempts($request),
            'remaining' => self::remaining($request, $limit),
            'limit' => $limit,
            'decay' => $decay,
            'reset_at' => self::resetAt($request, $decay),
            'retry_after' => self::retryAfter($request),
            'is_limited' => self::isLimited($request, $limit, $decay),
            'multiplier' => self::getMultiplier($request),
            'adjusted_limit' => self::getAdjustedLimit($request, $limit),
        ];
    }
}
