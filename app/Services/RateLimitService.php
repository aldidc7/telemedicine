<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

/**
 * Rate Limit Service
 * 
 * Service untuk menangani rate limiting pada berbagai aksi di aplikasi
 * Menggunakan cache untuk menyimpan attempt counter
 */
class RateLimitService
{
    // Register Rate Limits
    public const REGISTER_MAX_ATTEMPTS = 5;
    public const REGISTER_DECAY_MINUTES = 60;

    // Login Rate Limits
    public const LOGIN_MAX_ATTEMPTS = 5;
    public const LOGIN_DECAY_MINUTES = 15;

    // Forgot Password Rate Limits
    public const FORGOT_PASSWORD_MAX_ATTEMPTS = 3;
    public const FORGOT_PASSWORD_DECAY_MINUTES = 60;

    // Verify OTP Rate Limits
    public const VERIFY_OTP_MAX_ATTEMPTS = 5;
    public const VERIFY_OTP_DECAY_MINUTES = 15;

    // Resend OTP Rate Limits
    public const RESEND_OTP_MAX_ATTEMPTS = 3;
    public const RESEND_OTP_DECAY_MINUTES = 60;

    /**
     * Check if action is rate limited
     * 
     * @param string $key - Cache key (e.g., "register:user@example.com")
     * @param int $maxAttempts - Maximum allowed attempts
     * @param int $decayMinutes - Minutes before counter resets
     * @return bool - True if rate limited, false if allowed
     */
    public static function isLimited(string $key, int $maxAttempts, int $decayMinutes): bool
    {
        $attempts = Cache::get($key, 0);
        return $attempts >= $maxAttempts;
    }

    /**
     * Increment attempt counter
     * 
     * @param string $key - Cache key
     * @param int $decayMinutes - Minutes before counter resets
     * @return int - Current attempt count
     */
    public static function increment(string $key, int $decayMinutes): int
    {
        $attempts = Cache::get($key, 0);
        $attempts++;

        // Store in cache with TTL
        Cache::put($key, $attempts, now()->addMinutes($decayMinutes));

        return $attempts;
    }

    /**
     * Get current attempt count
     * 
     * @param string $key - Cache key
     * @return int - Current attempt count
     */
    public static function getAttempts(string $key): int
    {
        return Cache::get($key, 0);
    }

    /**
     * Get remaining attempts allowed
     * 
     * @param string $key - Cache key
     * @param int $maxAttempts - Maximum allowed attempts
     * @return int - Remaining attempts
     */
    public static function remaining(string $key, int $maxAttempts): int
    {
        $attempts = Cache::get($key, 0);
        return max(0, $maxAttempts - $attempts);
    }

    /**
     * Get retry after seconds
     * 
     * @param string $key - Cache key
     * @return int - Seconds until retry is allowed
     */
    public static function retryAfter(string $key): int
    {
        // Get the expiration time from cache
        // Since we can't directly get TTL, we'll return a default value
        // Or users can implement custom logic based on their cache driver
        return 60; // Default to 1 minute retry
    }

    /**
     * Reset attempt counter
     * 
     * @param string $key - Cache key
     * @return void
     */
    public static function reset(string $key): void
    {
        Cache::forget($key);
    }

    /**
     * Reset multiple keys
     * 
     * @param array $keys - Array of cache keys
     * @return void
     */
    public static function resetMultiple(array $keys): void
    {
        Cache::forget($keys);
    }

    /**
     * Clear all rate limit keys (for testing)
     * 
     * @return void
     */
    public static function flushAll(): void
    {
        Cache::flush();
    }
}
