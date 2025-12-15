<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Advanced Caching Strategy Service
 * 
 * Implements cache warming, invalidation, tagging, and monitoring
 * Manages all cache-related operations across the application
 */
class AdvancedCacheService
{
    /**
     * Cache keys and their TTLs
     */
    private const CACHE_CONFIG = [
        'slots' => [
            'ttl' => 900, // 15 minutes
            'tag' => 'appointments',
        ],
        'doctor_availability' => [
            'ttl' => 1800, // 30 minutes
            'tag' => 'doctors',
        ],
        'patient_appointments' => [
            'ttl' => 300, // 5 minutes
            'tag' => 'patient_data',
        ],
        'doctor_statistics' => [
            'ttl' => 3600, // 1 hour
            'tag' => 'dashboard',
        ],
        'consultation_list' => [
            'ttl' => 300, // 5 minutes
            'tag' => 'consultations',
        ],
        'prescription_list' => [
            'ttl' => 600, // 10 minutes
            'tag' => 'prescriptions',
        ],
        'rating_average' => [
            'ttl' => 7200, // 2 hours
            'tag' => 'ratings',
        ],
        'user_profile' => [
            'ttl' => 1800, // 30 minutes
            'tag' => 'users',
        ],
    ];

    /**
     * Get cached available slots for a doctor on a specific date
     */
    public function getAvailableSlots(int $doctorId, string $date, callable $callback)
    {
        $key = "slots:{$doctorId}:{$date}";
        $config = self::CACHE_CONFIG['slots'];

        return Cache::tags($config['tag'])
            ->remember($key, $config['ttl'], $callback);
    }

    /**
     * Get doctor availability with caching
     */
    public function getDoctorAvailability(int $doctorId, callable $callback)
    {
        $key = "doctor_availability:{$doctorId}";
        $config = self::CACHE_CONFIG['doctor_availability'];

        return Cache::tags($config['tag'])
            ->remember($key, $config['ttl'], $callback);
    }

    /**
     * Get patient appointments with caching
     */
    public function getPatientAppointments(int $patientId, callable $callback)
    {
        $key = "patient_appointments:{$patientId}";
        $config = self::CACHE_CONFIG['patient_appointments'];

        return Cache::tags($config['tag'])
            ->remember($key, $config['ttl'], $callback);
    }

    /**
     * Get doctor statistics for dashboard
     */
    public function getDoctorStatistics(int $doctorId, callable $callback)
    {
        $key = "doctor_statistics:{$doctorId}";
        $config = self::CACHE_CONFIG['doctor_statistics'];

        return Cache::tags($config['tag'])
            ->remember($key, $config['ttl'], $callback);
    }

    /**
     * Get doctor consultations with caching
     */
    public function getDoctorConsultations(int $doctorId, callable $callback)
    {
        $key = "consultation_list:doctor:{$doctorId}";
        $config = self::CACHE_CONFIG['consultation_list'];

        return Cache::tags($config['tag'])
            ->remember($key, $config['ttl'], $callback);
    }

    /**
     * Get patient consultations with caching
     */
    public function getPatientConsultations(int $patientId, callable $callback)
    {
        $key = "consultation_list:patient:{$patientId}";
        $config = self::CACHE_CONFIG['consultation_list'];

        return Cache::tags($config['tag'])
            ->remember($key, $config['ttl'], $callback);
    }

    /**
     * Get prescriptions with caching
     */
    public function getPrescriptions(int $consultationId, callable $callback)
    {
        $key = "prescription_list:{$consultationId}";
        $config = self::CACHE_CONFIG['prescription_list'];

        return Cache::tags($config['tag'])
            ->remember($key, $config['ttl'], $callback);
    }

    /**
     * Get doctor rating average with caching
     */
    public function getDoctorRatingAverage(int $doctorId, callable $callback)
    {
        $key = "rating_average:doctor:{$doctorId}";
        $config = self::CACHE_CONFIG['rating_average'];

        return Cache::tags($config['tag'])
            ->remember($key, $config['ttl'], $callback);
    }

    /**
     * Get user profile with caching
     */
    public function getUserProfile(int $userId, callable $callback)
    {
        $key = "user_profile:{$userId}";
        $config = self::CACHE_CONFIG['user_profile'];

        return Cache::tags($config['tag'])
            ->remember($key, $config['ttl'], $callback);
    }

    /**
     * Invalidate specific cache by tag
     */
    public function invalidateByTag(string $tag): void
    {
        Cache::tags($tag)->flush();
        Log::info("Cache invalidated for tag: {$tag}");
        $this->recordCacheEvent('invalidation', $tag);
    }

    /**
     * Invalidate appointment-related caches
     */
    public function invalidateAppointmentCache(int $doctorId, ?int $patientId = null): void
    {
        // Invalidate doctor availability
        Cache::tags('appointments')->flush();
        
        // Invalidate doctor statistics
        Cache::tags('dashboard')->flush();

        if ($patientId) {
            // Invalidate specific patient appointments
            Cache::forget("patient_appointments:{$patientId}");
        }

        Log::info("Appointment caches invalidated", [
            'doctor_id' => $doctorId,
            'patient_id' => $patientId
        ]);

        $this->recordCacheEvent('appointment_invalidation', 'appointments', [
            'doctor_id' => $doctorId,
            'patient_id' => $patientId
        ]);
    }

    /**
     * Invalidate consultation-related caches
     */
    public function invalidateConsultationCache(int $consultationId): void
    {
        Cache::tags('consultations')->flush();
        Cache::tags('ratings')->flush();

        Log::info("Consultation caches invalidated", [
            'consultation_id' => $consultationId
        ]);

        $this->recordCacheEvent('consultation_invalidation', 'consultations', [
            'consultation_id' => $consultationId
        ]);
    }

    /**
     * Invalidate user-related caches
     */
    public function invalidateUserCache(int $userId): void
    {
        Cache::forget("user_profile:{$userId}");
        Cache::forget("patient_appointments:{$userId}");
        Cache::tags('users')->flush();

        Log::info("User caches invalidated", [
            'user_id' => $userId
        ]);

        $this->recordCacheEvent('user_invalidation', 'users', [
            'user_id' => $userId
        ]);
    }

    /**
     * Warm cache for frequently accessed data
     */
    public function warmCache(): void
    {
        $startTime = microtime(true);
        $warmedCount = 0;

        try {
            // Warm available slots for all doctors for next 7 days
            $doctors = \App\Models\User::where('role', 'dokter')->get();
            
            foreach ($doctors as $doctor) {
                for ($i = 0; $i < 7; $i++) {
                    $date = Carbon::now()->addDays($i)->format('Y-m-d');
                    
                    Cache::tags('appointments')->remember(
                        "slots:{$doctor->id}:{$date}",
                        900,
                        fn() => $this->generateSlots($doctor->id, $date)
                    );
                    
                    $warmedCount++;
                }
            }

            // Warm doctor availability
            foreach ($doctors as $doctor) {
                Cache::tags('doctors')->remember(
                    "doctor_availability:{$doctor->id}",
                    1800,
                    fn() => $this->getDoctorAvailabilityData($doctor->id)
                );
            }

            $duration = microtime(true) - $startTime;

            Log::info("Cache warming completed", [
                'items_warmed' => $warmedCount,
                'duration_seconds' => round($duration, 2)
            ]);

            $this->recordCacheEvent('warming', 'all', [
                'items_warmed' => $warmedCount,
                'duration' => $duration
            ]);

        } catch (\Exception $e) {
            Log::error("Cache warming failed", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Get cache statistics
     */
    public function getCacheStats(): array
    {
        return [
            'driver' => config('cache.default'),
            'connection' => config('cache.stores.' . config('cache.default')),
            'hit_rate' => $this->calculateHitRate(),
            'size_estimate' => $this->estimateCacheSize(),
            'tags' => array_keys(self::CACHE_CONFIG),
            'total_ttl_sum' => array_sum(array_column(self::CACHE_CONFIG, 'ttl')),
        ];
    }

    /**
     * Calculate cache hit rate
     */
    private function calculateHitRate(): float
    {
        $hits = Cache::get('cache_hits', 0);
        $misses = Cache::get('cache_misses', 0);
        $total = $hits + $misses;

        if ($total === 0) {
            return 0;
        }

        return ($hits / $total) * 100;
    }

    /**
     * Estimate cache size (approximate)
     */
    private function estimateCacheSize(): string
    {
        // This is a rough estimate based on typical data sizes
        $estimatedKb = 50; // Base estimate

        if (Cache::has('cache_size_bytes')) {
            $bytes = Cache::get('cache_size_bytes');
            return $bytes . ' bytes';
        }

        return $estimatedKb . ' KB (estimated)';
    }

    /**
     * Record cache event for monitoring
     */
    private function recordCacheEvent(string $type, string $tag, array $data = []): void
    {
        $event = [
            'type' => $type,
            'tag' => $tag,
            'timestamp' => now(),
            'data' => $data
        ];

        // Store in cache for monitoring dashboard
        $events = Cache::get('cache_events', []);
        $events[] = $event;

        // Keep only last 100 events
        if (count($events) > 100) {
            $events = array_slice($events, -100);
        }

        Cache::put('cache_events', $events, 86400); // 1 day
    }

    /**
     * Get recent cache events
     */
    public function getRecentEvents(int $limit = 20): array
    {
        $events = Cache::get('cache_events', []);
        return array_slice($events, -$limit);
    }

    /**
     * Clear all caches
     */
    public function clearAll(): void
    {
        Cache::flush();
        Log::warning("All caches cleared");
        $this->recordCacheEvent('clear_all', 'all');
    }

    /**
     * Helper: Generate doctor slots
     */
    private function generateSlots(int $doctorId, string $date): array
    {
        // Implementation would fetch actual slots
        return [];
    }

    /**
     * Helper: Get doctor availability data
     */
    private function getDoctorAvailabilityData(int $doctorId): array
    {
        // Implementation would fetch actual availability
        return [];
    }

    /**
     * Get cache key for monitoring
     */
    public function getCacheKey(string $name, ...$identifiers): string
    {
        return $name . ':' . implode(':', $identifiers);
    }

    /**
     * Increment cache hit counter
     */
    public function recordHit(): void
    {
        Cache::increment('cache_hits', 1, 86400);
    }

    /**
     * Increment cache miss counter
     */
    public function recordMiss(): void
    {
        Cache::increment('cache_misses', 1, 86400);
    }
}
