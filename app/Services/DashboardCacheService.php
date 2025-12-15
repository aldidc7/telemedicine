<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use App\Models\User;
use App\Models\Pasien;
use App\Models\Dokter;
use App\Models\Konsultasi;

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
    // Cache TTL (Time To Live) dalam detik
    const CACHE_TTL_SHORT = 300;      // 5 minutes untuk data real-time
    const CACHE_TTL_MEDIUM = 900;     // 15 minutes untuk stats
    const CACHE_TTL_LONG = 3600;      // 1 hour untuk trends

    // Cache keys
    const KEY_USER_STATS = 'dashboard_user_stats';
    const KEY_CONSULTATION_STATS = 'dashboard_consultation_stats';
    const KEY_DOCTOR_STATS = 'dashboard_doctor_stats';
    const KEY_PATIENT_STATS = 'dashboard_patient_stats';
    const KEY_TRENDS = 'dashboard_trends';

    /**
     * Get atau compute user statistics dengan caching
     * 
     * @return array
     */
    public static function getUserStats()
    {
        return Cache::remember(self::KEY_USER_STATS, self::CACHE_TTL_MEDIUM, function () {
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
        return Cache::remember(self::KEY_CONSULTATION_STATS, self::CACHE_TTL_MEDIUM, function () {
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
        return Cache::remember(self::KEY_DOCTOR_STATS, self::CACHE_TTL_MEDIUM, function () {
            $totalDokter = Dokter::count();
            $dokterTersedia = Dokter::where('is_available', true)->count();
            $dokterBySpesialisasi = Dokter::selectRaw('spesialisasi, COUNT(*) as count')
                ->groupBy('spesialisasi')
                ->pluck('count', 'spesialisasi')
                ->toArray();

            return [
                'total' => $totalDokter,
                'tersedia' => $dokterTersedia,
                'tidak_tersedia' => $totalDokter - $dokterTersedia,
                'by_spesialisasi' => $dokterBySpesialisasi,
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
        return Cache::remember(self::KEY_PATIENT_STATS, self::CACHE_TTL_MEDIUM, function () {
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
                'lansia' => Pasien::where('tgl_lahir', '<', now()->subYears(60))->count(),
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
        return Cache::remember(self::KEY_TRENDS, self::CACHE_TTL_LONG, function () {
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
        return [
            'generated_at' => now(),
            'user_stats' => self::getUserStats(),
            'consultation_stats' => self::getConsultationStats(),
            'doctor_stats' => self::getDoctorStats(),
            'patient_stats' => self::getPatientStats(),
            'trends' => self::getTrends(),
        ];
    }
}
