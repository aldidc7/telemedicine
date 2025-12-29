<?php

namespace App\Services;

use App\Models\User;

/**
 * Rate Limiting Service
 * 
 * Service untuk menangani rate limiting berdasarkan user tier/role
 * Digunakan oleh PerformanceMiddleware untuk performance monitoring
 */
class RateLimitingService
{
    // Rate limit tiers
    private const TIER_GUEST = 'guest';
    private const TIER_USER = 'user';
    private const TIER_PREMIUM = 'premium';
    private const TIER_ADMIN = 'admin';

    // Requests per minute per tier
    private const LIMITS = [
        self::TIER_GUEST => 30,      // 30 req/min
        self::TIER_USER => 100,      // 100 req/min
        self::TIER_PREMIUM => 300,   // 300 req/min
        self::TIER_ADMIN => 1000,    // 1000 req/min
    ];

    /**
     * Get user tier based on role/subscription
     * 
     * @param User|null $user
     * @return string
     */
    public static function getUserTier(?User $user): string
    {
        if (!$user) {
            return self::TIER_GUEST;
        }

        if ($user->role === 'admin') {
            return self::TIER_ADMIN;
        }

        if ($user->role === 'dokter') {
            return self::TIER_PREMIUM;
        }

        return self::TIER_USER;
    }

    /**
     * Check if request is within rate limit
     * 
     * @param string $identifier - IP address or user ID
     * @param string $tier - User tier
     * @return array
     */
    public static function checkLimit(string $identifier, string $tier): array
    {
        $limit = self::LIMITS[$tier] ?? self::LIMITS[self::TIER_GUEST];
        $key = "rate_limit:{$tier}:{$identifier}";

        // Get current count from cache
        $count = \Illuminate\Support\Facades\Cache::get($key, 0);

        $allowed = $count < $limit;

        // Increment counter
        if ($allowed) {
            \Illuminate\Support\Facades\Cache::put($key, $count + 1, now()->addMinute());
        }

        return [
            'allowed' => $allowed,
            'limit' => $limit,
            'current' => $count,
            'remaining' => max(0, $limit - $count - 1),
            'reset' => now()->addMinute()->timestamp,
        ];
    }

    /**
     * Get rate limit headers for response
     * 
     * @param array $limitData
     * @return array
     */
    public static function getHeaders(array $limitData): array
    {
        return [
            'X-RateLimit-Limit' => (string)$limitData['limit'],
            'X-RateLimit-Remaining' => (string)$limitData['remaining'],
            'X-RateLimit-Reset' => (string)$limitData['reset'],
        ];
    }

    /**
     * Get limit for a specific tier
     * 
     * @param string $tier
     * @return int
     */
    public static function getLimit(string $tier): int
    {
        return self::LIMITS[$tier] ?? self::LIMITS[self::TIER_GUEST];
    }

    /**
     * Reset rate limit for identifier
     * 
     * @param string $identifier
     * @param string $tier
     * @return void
     */
    public static function resetLimit(string $identifier, string $tier): void
    {
        $key = "rate_limit:{$tier}:{$identifier}";
        \Illuminate\Support\Facades\Cache::forget($key);
    }
}
