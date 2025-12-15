<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use App\Models\User;
use App\Models\Pasien;
use App\Models\Dokter;
use App\Models\Konsultasi;
use App\Helpers\DateHelper;

/**
 * ============================================
 * DASHBOARD CACHE SERVICE
 * ============================================
 * 
 * Caching strategy untuk dashboard statistics
 * Mengurangi database load dengan smart caching
 */
class DashboardCacheService
{
    // Cache keys
    const KEY_USER_STATS = 'dashboard_user_stats';
    const KEY_CONSULTATION_STATS = 'dashboard_consultation_stats';
    const KEY_DOCTOR_STATS = 'dashboard_doctor_stats';
    const KEY_PATIENT_STATS = 'dashboard_patient_stats';
    const KEY_TRENDS = 'dashboard_trends';

    /**
     * Get cache TTL values dari config
     */
    private static function getCacheTTL(string $type = 'short'): int
    {
        return config('appointment.CACHE_TTL')[$type] ?? 300;
    }

    /**
     * Get atau compute user statistics dengan caching
     * 
     * @return array
     */
    public static function getUserStats()
    {
        return Cache::remember(self::KEY_USER_STATS, self::getCacheTTL('medium'), function () {
            $totalUsers = User::count();
            $userAktif = User::where('is_active', true)->count();
            
            return [
                'total' => $totalUsers,
                'aktif' => $userAktif,
                'tidak_aktif' => $totalUsers - $userAktif,
            ];
        });
    }

    /**
     * Get atau compute consultation statistics dengan caching
     * 
     * @return array
     */
    public static function getConsultationStats()
    {
        return Cache::remember(self::KEY_CONSULTATION_STATS, self::getCacheTTL('medium'), function () {
            $totalKonsultasi = Konsultasi::count();
            $konsultasiByStatus = [
                'pending' => Konsultasi::where('status', 'pending')->count(),
                'active' => Konsultasi::where('status', 'active')->count(),
                'closed' => Konsultasi::where('status', 'closed')->count(),
                'cancelled' => Konsultasi::where('status', 'cancelled')->count(),
            ];

            return [
                'total' => $totalKonsultasi,
                'by_status' => $konsultasiByStatus,
            ];
        });
    }

    /**
     * Get atau compute doctor statistics dengan caching
     * 
     * @return array
     */
    public static function getDoctorStats()
    {
        return Cache::remember(self::KEY_DOCTOR_STATS, self::getCacheTTL('medium'), function () {
            $totalDokter = Dokter::count();
            $dokterTersedia = Dokter::where('is_available', true)->count();
            $dokterBySpesialisasi = Dokter::selectRaw('specialization, COUNT(*) as count')
                ->groupBy('specialization')
                ->pluck('count', 'specialization')
                ->toArray();

            return [
                'total' => $totalDokter,
                'tersedia' => $dokterTersedia,
                'tidak_tersedia' => $totalDokter - $dokterTersedia,
                'by_specialization' => $dokterBySpesialisasi,
            ];
        });
    }

    /**
     * Get atau compute patient statistics dengan caching
     * 
     * @return array
     */
    public static function getPatientStats()
    {
        return Cache::remember(self::KEY_PATIENT_STATS, self::getCacheTTL('medium'), function () {
            $totalPasien = Pasien::count();
            $pasienAktif = User::where('role', 'pasien')
                ->where('is_active', true)
                ->count();

            // Age group statistics (optimized with whereBetween)
            $pasienByAgeGroup = [
                'anak_anak' => Pasien::where('tgl_lahir', '>', now()->subYears(12))->count(),
                'remaja' => Pasien::whereBetween('tgl_lahir', [
                    now()->subYears(17)->startOfYear(),
                    now()->subYears(12)->endOfYear()
                ])->count(),
                'dewasa' => Pasien::whereBetween('tgl_lahir', [
                    now()->subYears(60)->startOfYear(),
                    now()->subYears(18)->endOfYear()
                ])->count(),
                'lansia' => Pasien::where('tgl_lahir', '<', now()->subYears(config('application.LANSIA_AGE_THRESHOLD', 60)))->count(),
            ];

            return [
                'total' => $totalPasien,
                'aktif' => $pasienAktif,
                'tidak_aktif' => $totalPasien - $pasienAktif,
                'by_age_group' => $pasienByAgeGroup,
            ];
        });
    }

    /**
     * Get atau compute trends (konsultasi per bulan) dengan caching
     * 
     * @return array
     */
    public static function getTrends()
    {
        return Cache::remember(self::KEY_TRENDS, self::getCacheTTL('long'), function () {
            $konsultasiPerBulan = Konsultasi::selectRaw('strftime("%Y-%m", created_at) as bulan, COUNT(*) as count')
                ->where('created_at', '>=', now()->subMonths(12))
                ->groupBy('bulan')
                ->orderBy('bulan')
                ->pluck('count', 'bulan')
                ->toArray();

            return [
                'konsultasi_per_bulan' => $konsultasiPerBulan,
            ];
        });
    }

    /**
     * Clear cache ketika ada perubahan data
     * Dipanggil otomatis via model observers
     * 
     * @param string|null $key - Specific key atau null untuk clear semua
     * @return void
     */
    public static function clearCache($key = null)
    {
        if ($key) {
            Cache::forget($key);
        } else {
            // Clear semua dashboard cache
            Cache::forget(self::KEY_USER_STATS);
            Cache::forget(self::KEY_CONSULTATION_STATS);
            Cache::forget(self::KEY_DOCTOR_STATS);
            Cache::forget(self::KEY_PATIENT_STATS);
            Cache::forget(self::KEY_TRENDS);
        }
    }

    /**
     * Get all dashboard statistics
     * 
     * @return array
     */
    public static function getAllStats()
    {
        try {
            return [
                'generated_at' => now(),
                'user_stats' => self::getUserStats(),
                'consultation_stats' => self::getConsultationStats(),
                'doctor_stats' => self::getDoctorStats(),
                'patient_stats' => self::getPatientStats(),
                'trends' => self::getTrends(),
            ];
        } catch (\Exception $e) {
            // Log error and return empty stats
            \Log::error('DashboardCacheService error: ' . $e->getMessage());
            return [
                'generated_at' => now(),
                'error' => $e->getMessage(),
            ];
        }
    }
}
