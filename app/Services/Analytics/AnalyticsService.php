<?php

namespace App\Services\Analytics;

use App\Models\User;
use App\Models\Dokter;
use App\Models\Konsultasi;
use App\Models\Payment;
use App\Models\Appointment;
use App\Models\Rating;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    // Cache duration (in minutes)
    const CACHE_DURATION = 60;
    
    // Cache keys
    const CACHE_DASHBOARD = 'analytics.dashboard';
    const CACHE_METRICS = 'analytics.metrics';
    const CACHE_USERS = 'analytics.users';
    const CACHE_CONSULTATIONS = 'analytics.consultations';
    const CACHE_PAYMENTS = 'analytics.payments';

    /**
     * Get dashboard metrics
     */
    public function getDashboardMetrics(): array
    {
        return Cache::remember(self::CACHE_DASHBOARD, self::CACHE_DURATION * 60, function () {
            return [
                'total_users' => $this->getTotalUsers(),
                'active_users' => $this->getActiveUsers(),
                'total_doctors' => $this->getTotalDoctors(),
                'total_consultations' => $this->getTotalConsultations(),
                'completed_consultations' => $this->getCompletedConsultations(),
                'total_revenue' => $this->getTotalRevenue(),
                'average_rating' => $this->getAverageRating(),
                'system_health' => $this->getSystemHealth(),
                'timestamp' => now(),
            ];
        });
    }

    /**
     * Get key metrics
     */
    public function getKeyMetrics(string $period = 'monthly'): array
    {
        $cacheKey = self::CACHE_METRICS . ".$period";
        
        return Cache::remember($cacheKey, self::CACHE_DURATION * 60, function () use ($period) {
            return [
                'period' => $period,
                'user_growth' => $this->getUserGrowth($period),
                'consultation_trend' => $this->getConsultationTrend($period),
                'revenue_trend' => $this->getRevenueTrend($period),
                'rating_trend' => $this->getRatingTrend($period),
                'top_metrics' => [
                    'users_this_month' => $this->getUsersThisMonth(),
                    'consultations_this_month' => $this->getConsultationsThisMonth(),
                    'revenue_this_month' => $this->getRevenueThisMonth(),
                ],
            ];
        });
    }

    /**
     * Get user activity trends
     */
    public function getUserActivityTrends(string $days = '30'): array
    {
        $startDate = Carbon::now()->subDays($days);
        
        return [
            'period' => "{$days} days",
            'daily_active_users' => $this->getDailyActiveUsers($startDate),
            'user_signups' => $this->getUserSignups($startDate),
            'user_retention' => $this->getUserRetention($startDate),
            'user_segments' => $this->getUserSegments(),
        ];
    }

    /**
     * Get consultation statistics
     */
    public function getConsultationStats(): array
    {
        return Cache::remember(self::CACHE_CONSULTATIONS, self::CACHE_DURATION * 60, function () {
            return [
                'total_consultations' => Konsultasi::count(),
                'completed' => Konsultasi::where('status', 'selesai')->count(),
                'ongoing' => Konsultasi::where('status', 'berlangsung')->count(),
                'pending' => Konsultasi::where('status', 'pending')->count(),
                'cancelled' => Konsultasi::where('status', 'batal')->count(),
                'average_duration' => $this->getAverageDuration(),
                'peak_hours' => $this->getPeakConsultationHours(),
                'by_type' => $this->getConsultationsByType(),
            ];
        });
    }

    /**
     * Get payment metrics
     */
    public function getPaymentMetrics(): array
    {
        return Cache::remember(self::CACHE_PAYMENTS, self::CACHE_DURATION * 60, function () {
            return [
                'total_payments' => Payment::count(),
                'completed_payments' => Payment::where('status', 'completed')->count(),
                'total_amount' => Payment::where('status', 'completed')->sum('amount'),
                'average_payment' => Payment::where('status', 'completed')->avg('amount'),
                'payment_methods' => $this->getPaymentMethods(),
                'refunds_total' => Payment::where('status', 'refunded')->sum('amount'),
                'pending_payments' => Payment::where('status', 'pending')->sum('amount'),
            ];
        });
    }

    /**
     * Export analytics to CSV
     */
    public function exportToCSV(array $metrics): string
    {
        $csv = "Metric,Value,Date\n";
        
        foreach ($metrics as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $subKey => $subValue) {
                    $csv .= "$key.$subKey,$subValue," . now()->toDateString() . "\n";
                }
            } else {
                $csv .= "$key,$value," . now()->toDateString() . "\n";
            }
        }
        
        return $csv;
    }

    /**
     * Clear cache
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_DASHBOARD);
        Cache::forget(self::CACHE_METRICS);
        Cache::forget(self::CACHE_USERS);
        Cache::forget(self::CACHE_CONSULTATIONS);
        Cache::forget(self::CACHE_PAYMENTS);
    }

    // ===== Private Helper Methods =====

    private function getTotalUsers(): int
    {
        return User::count();
    }

    private function getActiveUsers(): int
    {
        return User::where('last_activity', '>=', Carbon::now()->subDays(30))->count();
    }

    private function getTotalDoctors(): int
    {
        return Dokter::count();
    }

    private function getTotalConsultations(): int
    {
        return Konsultasi::count();
    }

    private function getCompletedConsultations(): int
    {
        return Konsultasi::where('status', 'selesai')->count();
    }

    private function getTotalRevenue(): float
    {
        return (float) Payment::where('status', 'completed')->sum('amount');
    }

    private function getAverageRating(): float
    {
        return (float) Rating::avg('rating') ?? 0;
    }

    private function getSystemHealth(): array
    {
        $totalUsers = $this->getTotalUsers();
        $activeUsers = $this->getActiveUsers();
        $consultationCompletion = $this->getCompletedConsultations() / max($this->getTotalConsultations(), 1);
        
        $health = min(100, (
            ($activeUsers / max($totalUsers, 1)) * 40 +
            $consultationCompletion * 60
        ));
        
        return [
            'score' => round($health, 2),
            'status' => $health >= 80 ? 'healthy' : ($health >= 60 ? 'normal' : 'warning'),
            'active_users_ratio' => round(($activeUsers / max($totalUsers, 1)) * 100, 2),
            'completion_ratio' => round($consultationCompletion * 100, 2),
        ];
    }

    private function getUserGrowth(string $period): array
    {
        $groupBy = $period === 'daily' ? 'DATE(created_at)' : 'DATE_TRUNC(\'month\', created_at)';
        
        $growth = User::selectRaw("$groupBy as period, COUNT(*) as count")
            ->where('created_at', '>=', Carbon::now()->subMonths(12))
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->pluck('count', 'period')
            ->toArray();
        
        return $growth;
    }

    private function getConsultationTrend(string $period): array
    {
        $groupBy = $period === 'daily' ? 'DATE(created_at)' : 'DATE_TRUNC(\'month\', created_at)';
        
        $trend = Konsultasi::selectRaw("$groupBy as period, COUNT(*) as count")
            ->where('created_at', '>=', Carbon::now()->subMonths(12))
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->pluck('count', 'period')
            ->toArray();
        
        return $trend;
    }

    private function getRevenueTrend(string $period): array
    {
        $groupBy = $period === 'daily' ? 'DATE(created_at)' : 'DATE_TRUNC(\'month\', created_at)';
        
        $trend = Payment::selectRaw("$groupBy as period, SUM(amount) as total")
            ->where('status', 'completed')
            ->where('created_at', '>=', Carbon::now()->subMonths(12))
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->pluck('total', 'period')
            ->toArray();
        
        return $trend;
    }

    private function getRatingTrend(string $period): array
    {
        $groupBy = $period === 'daily' ? 'DATE(created_at)' : 'DATE_TRUNC(\'month\', created_at)';
        
        $trend = Rating::selectRaw("$groupBy as period, AVG(rating) as avg_rating")
            ->where('created_at', '>=', Carbon::now()->subMonths(12))
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->pluck('avg_rating', 'period')
            ->toArray();
        
        return $trend;
    }

    private function getUsersThisMonth(): int
    {
        return User::where('created_at', '>=', Carbon::now()->startOfMonth())->count();
    }

    private function getConsultationsThisMonth(): int
    {
        return Konsultasi::where('created_at', '>=', Carbon::now()->startOfMonth())->count();
    }

    private function getRevenueThisMonth(): float
    {
        return (float) Payment::where('status', 'completed')
            ->where('created_at', '>=', Carbon::now()->startOfMonth())
            ->sum('amount');
    }

    private function getDailyActiveUsers(Carbon $startDate): array
    {
        $daily = [];
        
        for ($i = 0; $i < 30; $i++) {
            $date = $startDate->copy()->addDays($i)->toDateString();
            $count = User::where('last_activity', '>=', Carbon::parse($date))
                ->where('last_activity', '<', Carbon::parse($date)->addDay())
                ->count();
            
            $daily[$date] = $count;
        }
        
        return $daily;
    }

    private function getUserSignups(Carbon $startDate): array
    {
        return User::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();
    }

    private function getUserRetention(Carbon $startDate): array
    {
        // Simplified retention calculation
        $newUsers = User::where('created_at', '>=', $startDate)->count();
        $returningUsers = User::where('created_at', '<', $startDate)
            ->where('last_activity', '>=', $startDate)
            ->count();
        
        $totalActive = $newUsers + $returningUsers;
        
        return [
            'new_users' => $newUsers,
            'returning_users' => $returningUsers,
            'retention_rate' => $totalActive > 0 ? round(($returningUsers / $totalActive) * 100, 2) : 0,
        ];
    }

    private function getUserSegments(): array
    {
        return [
            'doctors' => Dokter::count(),
            'patients' => User::whereNotIn('id', Dokter::pluck('user_id'))->count(),
            'admins' => User::where('role', 'admin')->count(),
        ];
    }

    private function getAverageDuration(): int
    {
        // Average consultation duration in minutes
        return (int) Konsultasi::selectRaw('AVG(EXTRACT(EPOCH FROM (ended_at - started_at))/60) as avg_duration')
            ->where('status', 'selesai')
            ->where('started_at', '!=', null)
            ->where('ended_at', '!=', null)
            ->value('avg_duration') ?? 0;
    }

    private function getPeakConsultationHours(): array
    {
        $peaks = Konsultasi::selectRaw('EXTRACT(HOUR FROM started_at) as hour, COUNT(*) as count')
            ->where('started_at', '!=', null)
            ->groupBy('hour')
            ->orderByDesc('count')
            ->limit(5)
            ->pluck('count', 'hour')
            ->toArray();
        
        return $peaks;
    }

    private function getConsultationsByType(): array
    {
        return Konsultasi::selectRaw('tipe_konsultasi, COUNT(*) as count')
            ->groupBy('tipe_konsultasi')
            ->pluck('count', 'tipe_konsultasi')
            ->toArray();
    }

    private function getPaymentMethods(): array
    {
        return Payment::selectRaw('metode_pembayaran, COUNT(*) as count, SUM(amount) as total')
            ->where('status', 'completed')
            ->groupBy('metode_pembayaran')
            ->get()
            ->map(function ($item) {
                return [
                    'method' => $item->metode_pembayaran,
                    'count' => $item->count,
                    'total' => $item->total,
                ];
            })
            ->toArray();
    }
}
