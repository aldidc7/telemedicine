<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Rate Limiting Service
 * 
 * Manage rate limiting untuk prevent brute force attacks
 */
class RateLimitService
{
    const LOGIN_MAX_ATTEMPTS = 5;
    const LOGIN_DECAY_MINUTES = 15;
    const REGISTER_MAX_ATTEMPTS = 3;
    const REGISTER_DECAY_MINUTES = 60;
    const FORGOT_PASSWORD_MAX_ATTEMPTS = 3;
    const FORGOT_PASSWORD_DECAY_MINUTES = 60;

    /**
     * Check if user exceeded rate limit
     */
    public static function isLimited(string $key, int $maxAttempts, int $decayMinutes): bool
    {
        $rateLimit = DB::table('rate_limits')
            ->where('key', $key)
            ->where(function ($query) use ($decayMinutes) {
                $query->where('reset_at', '>', now())
                    ->orWhere(function ($q) use ($decayMinutes) {
                        $q->whereNull('reset_at')
                            ->where('last_attempt_at', '>', now()->subMinutes($decayMinutes));
                    });
            })
            ->first();

        if (!$rateLimit) {
            return false;
        }

        return $rateLimit->attempts >= $maxAttempts;
    }

    /**
     * Increment attempt counter
     */
    public static function increment(string $key, int $decayMinutes): void
    {
        $rateLimit = DB::table('rate_limits')
            ->where('key', $key)
            ->first();

        if (!$rateLimit) {
            // Create new rate limit record
            DB::table('rate_limits')->insert([
                'key' => $key,
                'attempts' => 1,
                'last_attempt_at' => now(),
                'reset_at' => now()->addMinutes($decayMinutes),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            // Increment existing
            DB::table('rate_limits')
                ->where('key', $key)
                ->update([
                    'attempts' => $rateLimit->attempts + 1,
                    'last_attempt_at' => now(),
                    'reset_at' => now()->addMinutes($decayMinutes),
                    'updated_at' => now(),
                ]);
        }
    }

    /**
     * Reset rate limit
     */
    public static function reset(string $key): void
    {
        DB::table('rate_limits')
            ->where('key', $key)
            ->delete();
    }

    /**
     * Get remaining attempts
     */
    public static function remaining(string $key, int $maxAttempts, int $decayMinutes): int
    {
        $rateLimit = DB::table('rate_limits')
            ->where('key', $key)
            ->where(function ($query) use ($decayMinutes) {
                $query->where('reset_at', '>', now())
                    ->orWhere(function ($q) use ($decayMinutes) {
                        $q->whereNull('reset_at')
                            ->where('last_attempt_at', '>', now()->subMinutes($decayMinutes));
                    });
            })
            ->first();

        if (!$rateLimit) {
            return $maxAttempts;
        }

        return max(0, $maxAttempts - $rateLimit->attempts);
    }

    /**
     * Get attempts count
     */
    public static function attempts(string $key, int $decayMinutes): int
    {
        $rateLimit = DB::table('rate_limits')
            ->where('key', $key)
            ->where(function ($query) use ($decayMinutes) {
                $query->where('reset_at', '>', now())
                    ->orWhere(function ($q) use ($decayMinutes) {
                        $q->whereNull('reset_at')
                            ->where('last_attempt_at', '>', now()->subMinutes($decayMinutes));
                    });
            })
            ->first();

        return $rateLimit ? $rateLimit->attempts : 0;
    }
}
