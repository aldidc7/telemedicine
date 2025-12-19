<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * API Rate Limiting Service
 * 
 * Prevent abuse dan ensure fair usage of API
 * Implements token bucket algorithm for rate limiting
 */
class RateLimitingService
{
    /**
     * Rate limit configurations
     */
    const RATE_LIMITS = [
        'public' => [
            'requests' => 60,
            'window' => 60, // seconds
        ],
        'authenticated' => [
            'requests' => 1000,
            'window' => 3600, // 1 hour
        ],
        'premium' => [
            'requests' => 5000,
            'window' => 3600,
        ],
        'admin' => [
            'requests' => 10000,
            'window' => 3600,
        ],
    ];

    /**
     * Check if request is rate limited
     *
     * @param string $identifier (IP address or user ID)
     * @param string $tier
     * @return array ['allowed' => bool, 'remaining' => int, 'reset_at' => timestamp]
     */
    public static function checkLimit(string $identifier, string $tier = 'authenticated'): array
    {
        $config = self::RATE_LIMITS[$tier] ?? self::RATE_LIMITS['public'];
        $cacheKey = "rate_limit:{$tier}:{$identifier}";

        // Get current request count
        $data = Cache::get($cacheKey, [
            'count' => 0,
            'reset_at' => now()->addSeconds($config['window'])->timestamp,
        ]);

        // Check if reset time has passed
        if ($data['reset_at'] <= now()->timestamp) {
            $data = [
                'count' => 0,
                'reset_at' => now()->addSeconds($config['window'])->timestamp,
            ];
        }

        // Increment count
        $data['count']++;

        // Check if limit exceeded
        $allowed = $data['count'] <= $config['requests'];
        $remaining = max(0, $config['requests'] - $data['count']);

        // Store updated data
        Cache::put($cacheKey, $data, $config['window']);

        if (!$allowed) {
            Log::warning("Rate limit exceeded for {$tier}:{$identifier}", [
                'tier' => $tier,
                'identifier' => $identifier,
                'count' => $data['count'],
                'limit' => $config['requests'],
            ]);
        }

        return [
            'allowed' => $allowed,
            'remaining' => $remaining,
            'reset_at' => $data['reset_at'],
            'limit' => $config['requests'],
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
            'X-RateLimit-Reset' => (string)$limitData['reset_at'],
        ];
    }

    /**
     * Get user tier based on subscription/role
     *
     * @param \App\Models\User|null $user
     * @return string
     */
    public static function getUserTier(?\App\Models\User $user = null): string
    {
        if (!$user) {
            return 'public';
        }

        if ($user->isAdmin()) {
            return 'admin';
        }

        if ($user->is_premium ?? false) {
            return 'premium';
        }

        return 'authenticated';
    }

    /**
     * Get all rate limit keys for identifier
     * Useful for admin debugging
     *
     * @param string $identifier
     * @return array
     */
    public static function getIdentifierLimits(string $identifier): array
    {
        $limits = [];

        foreach (array_keys(self::RATE_LIMITS) as $tier) {
            $cacheKey = "rate_limit:{$tier}:{$identifier}";
            $data = Cache::get($cacheKey);

            if ($data) {
                $limits[$tier] = $data;
            }
        }

        return $limits;
    }

    /**
     * Reset rate limit for identifier
     * Admin only
     *
     * @param string $identifier
     * @param string|null $tier (null = reset all tiers)
     * @return void
     */
    public static function resetLimit(string $identifier, ?string $tier = null): void
    {
        if ($tier) {
            Cache::forget("rate_limit:{$tier}:{$identifier}");
        } else {
            foreach (array_keys(self::RATE_LIMITS) as $t) {
                Cache::forget("rate_limit:{$t}:{$identifier}");
            }
        }

        Log::info("Rate limit reset for {$identifier}" . ($tier ? " (tier: {$tier})" : ""));
    }
}
