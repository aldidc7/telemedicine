<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

/**
 * ============================================
 * CACHE SERVICE
 * ============================================
 * 
 * Centralized caching untuk performance optimization
 */
class CacheService
{
    const CACHE_TTL_SHORT = 300; // 5 minutes
    const CACHE_TTL_MEDIUM = 3600; // 1 hour
    const CACHE_TTL_LONG = 86400; // 1 day

    /**
     * Get cached data atau call callback
     * 
     * @param string $key
     * @param callable $callback
     * @param int $ttl
     * @return mixed
     */
    public static function remember($key, $callback, $ttl = self::CACHE_TTL_MEDIUM)
    {
        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Get dokter list dengan cache
     */
    public static function getDokterList($filters = [])
    {
        $cacheKey = 'dokter_list_' . md5(json_encode($filters));
        
        return self::remember($cacheKey, function () use ($filters) {
            return \App\Models\Dokter::with('pengguna')
                ->when($filters['specialization'] ?? false, function ($q) use ($filters) {
                    $q->where('specialization', $filters['specialization']);
                })
                ->when($filters['is_available'] ?? false, function ($q) {
                    $q->where('is_available', true);
                })
                ->get();
        }, self::CACHE_TTL_MEDIUM);
    }

    /**
     * Get pasien list dengan cache
     */
    public static function getPasienList($filters = [])
    {
        $cacheKey = 'pasien_list_' . md5(json_encode($filters));
        
        return self::remember($cacheKey, function () {
            return \App\Models\Pasien::with('pengguna')->get();
        }, self::CACHE_TTL_LONG);
    }

    /**
     * Get konsultasi dengan cache
     */
    public static function getKonsultasi($id)
    {
        $cacheKey = "konsultasi_{$id}";
        
        return self::remember($cacheKey, function () use ($id) {
            return \App\Models\Konsultasi::with('pasien', 'dokter', 'pesan')
                ->find($id);
        }, self::CACHE_TTL_SHORT);
    }

    /**
     * Get dashboard stats dengan cache
     */
    public static function getDashboardStats()
    {
        $cacheKey = 'dashboard_stats';
        
        return self::remember($cacheKey, function () {
            return [
                'total_dokter' => \App\Models\Dokter::count(),
                'total_pasien' => \App\Models\Pasien::count(),
                'total_konsultasi' => \App\Models\Konsultasi::count(),
                'konsultasi_aktif' => \App\Models\Konsultasi::where('status', 'aktif')->count(),
            ];
        }, self::CACHE_TTL_SHORT);
    }

    /**
     * Invalidate cache
     */
    public static function invalidate($pattern = null)
    {
        if ($pattern) {
            Cache::flush(); // For simplicity, flush all. Use tagged cache untuk lebih fine-grained control
        } else {
            Cache::flush();
        }
    }

    /**
     * Invalidate dokter cache
     */
    public static function invalidateDokter()
    {
        // Flush patterns yang berisi 'dokter_'
        Cache::flush();
    }

    /**
     * Invalidate konsultasi cache
     */
    public static function invalidateKonsultasi($id = null)
    {
        if ($id) {
            Cache::forget("konsultasi_{$id}");
        } else {
            Cache::flush();
        }
    }

    /**
     * Invalidate dashboard cache
     */
    public static function invalidateDashboard()
    {
        Cache::forget('dashboard_stats');
    }
}
