<?php

namespace App\Services;

use App\Models\User;
use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\Rating;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Admin Dashboard Service
 * 
 * Provides comprehensive analytics and system metrics
 * for admin dashboard visualization
 */
class AdminDashboardService
{
    /**
     * Get complete dashboard overview
     */
    public function getDashboardOverview(): array
    {
        return [
            'summary' => $this->getSummary(),
            'appointments' => $this->getAppointmentMetrics(),
            'users' => $this->getUserMetrics(),
            'consultations' => $this->getConsultationMetrics(),
            'ratings' => $this->getRatingMetrics(),
            'system' => $this->getSystemMetrics(),
            'trends' => $this->getTrendMetrics(),
            'recent_activities' => $this->getRecentActivities(),
        ];
    }

    /**
     * Get summary statistics
     */
    public function getSummary(): array
    {
        return [
            'total_users' => User::count(),
            'total_doctors' => User::where('role', 'dokter')->count(),
            'total_patients' => User::where('role', 'pasien')->count(),
            'active_users_today' => $this->getActiveUsersToday(),
            'total_appointments' => Appointment::count(),
            'completed_appointments' => Appointment::where('status', 'completed')->count(),
            'pending_appointments' => Appointment::where('status', 'pending')->count(),
            'total_consultations' => Consultation::count(),
            'total_revenue' => $this->calculateRevenue(),
            'average_rating' => Rating::avg('rating') ?? 0,
        ];
    }

    /**
     * Get appointment metrics
     */
    public function getAppointmentMetrics(): array
    {
        $today = now()->startOfDay();
        $week = now()->startOfWeek();
        $month = now()->startOfMonth();

        return [
            'today' => [
                'total' => Appointment::whereDate('scheduled_at', $today)->count(),
                'pending' => Appointment::where('status', 'pending')
                    ->whereDate('scheduled_at', $today)
                    ->count(),
                'confirmed' => Appointment::where('status', 'confirmed')
                    ->whereDate('scheduled_at', $today)
                    ->count(),
                'completed' => Appointment::where('status', 'completed')
                    ->whereDate('scheduled_at', $today)
                    ->count(),
                'cancelled' => Appointment::where('status', 'cancelled')
                    ->whereDate('scheduled_at', $today)
                    ->count(),
            ],
            'this_week' => [
                'total' => Appointment::whereBetween('scheduled_at', [$week, now()])->count(),
                'online_count' => Appointment::where('type', 'online')
                    ->whereBetween('scheduled_at', [$week, now()])
                    ->count(),
                'offline_count' => Appointment::where('type', 'offline')
                    ->whereBetween('scheduled_at', [$week, now()])
                    ->count(),
            ],
            'this_month' => [
                'total' => Appointment::whereBetween('scheduled_at', [$month, now()])->count(),
                'completion_rate' => $this->calculateAppointmentCompletionRate($month),
                'cancellation_rate' => $this->calculateAppointmentCancellationRate($month),
            ],
            'status_distribution' => $this->getAppointmentStatusDistribution(),
            'type_distribution' => $this->getAppointmentTypeDistribution(),
        ];
    }

    /**
     * Get user metrics
     */
    public function getUserMetrics(): array
    {
        $week = now()->startOfWeek();
        $month = now()->startOfMonth();

        return [
            'total_users' => User::count(),
            'doctors' => [
                'total' => User::where('role', 'dokter')->count(),
                'active' => User::where('role', 'dokter')->where('active', true)->count(),
                'new_this_month' => User::where('role', 'dokter')
                    ->whereBetween('created_at', [$month, now()])
                    ->count(),
            ],
            'patients' => [
                'total' => User::where('role', 'pasien')->count(),
                'active' => User::where('role', 'pasien')->where('active', true)->count(),
                'new_this_month' => User::where('role', 'pasien')
                    ->whereBetween('created_at', [$month, now()])
                    ->count(),
            ],
            'admins' => [
                'total' => User::where('role', 'admin')->count(),
            ],
            'growth' => [
                'week_over_week' => $this->calculateUserGrowth('week'),
                'month_over_month' => $this->calculateUserGrowth('month'),
            ],
        ];
    }

    /**
     * Get consultation metrics
     */
    public function getConsultationMetrics(): array
    {
        $month = now()->startOfMonth();

        return [
            'total_consultations' => Consultation::count(),
            'status_breakdown' => [
                'scheduled' => Consultation::where('status', 'scheduled')->count(),
                'in_progress' => Consultation::where('status', 'in_progress')->count(),
                'completed' => Consultation::where('status', 'completed')->count(),
                'cancelled' => Consultation::where('status', 'cancelled')->count(),
            ],
            'this_month' => [
                'total' => Consultation::whereBetween('created_at', [$month, now()])->count(),
                'average_duration' => $this->calculateAverageConsultationDuration(),
            ],
            'by_doctor' => $this->getTopDoctorsByConsultations(5),
        ];
    }

    /**
     * Get rating metrics
     */
    public function getRatingMetrics(): array
    {
        $month = now()->startOfMonth();

        return [
            'total_ratings' => Rating::count(),
            'average_rating' => Rating::avg('rating') ?? 0,
            'rating_distribution' => [
                '5_stars' => Rating::where('rating', 5)->count(),
                '4_stars' => Rating::where('rating', 4)->count(),
                '3_stars' => Rating::where('rating', 3)->count(),
                '2_stars' => Rating::where('rating', 2)->count(),
                '1_star' => Rating::where('rating', 1)->count(),
            ],
            'this_month' => [
                'total' => Rating::whereBetween('created_at', [$month, now()])->count(),
                'average' => Rating::whereBetween('created_at', [$month, now()])->avg('rating') ?? 0,
            ],
            'top_doctors' => $this->getTopRatedDoctors(5),
        ];
    }

    /**
     * Get system metrics
     */
    public function getSystemMetrics(): array
    {
        return [
            'database' => [
                'tables' => $this->countDatabaseTables(),
                'size' => $this->getDatabaseSize(),
            ],
            'cache' => [
                'driver' => config('cache.default'),
                'status' => $this->checkCacheStatus(),
            ],
            'storage' => [
                'used' => $this->getStorageUsage(),
                'available' => $this->getAvailableStorage(),
                'percentage' => $this->getStoragePercentage(),
            ],
            'last_backup' => $this->getLastBackupTime(),
        ];
    }

    /**
     * Get trend metrics
     */
    public function getTrendMetrics(): array
    {
        return [
            'appointments_trend' => $this->getAppointmentsTrendData(30),
            'users_trend' => $this->getUsersTrendData(30),
            'ratings_trend' => $this->getRatingsTrendData(30),
            'consultations_trend' => $this->getConsultationsTrendData(30),
        ];
    }

    /**
     * Get recent activities
     */
    public function getRecentActivities(int $limit = 10): array
    {
        $activities = [];

        // Recent appointments
        $recentAppointments = Appointment::with('patient', 'doctor')
            ->latest('updated_at')
            ->limit($limit)
            ->get();

        foreach ($recentAppointments as $appointment) {
            $activities[] = [
                'type' => 'appointment',
                'patient' => $appointment->patient->name,
                'doctor' => $appointment->doctor->name,
                'status' => $appointment->status,
                'timestamp' => $appointment->updated_at,
            ];
        }

        // Recent registrations
        $recentUsers = User::latest('created_at')
            ->limit($limit)
            ->get();

        foreach ($recentUsers as $user) {
            $activities[] = [
                'type' => 'registration',
                'name' => $user->name,
                'role' => $user->role,
                'timestamp' => $user->created_at,
            ];
        }

        // Recent ratings
        $recentRatings = Rating::with('doctor')
            ->latest('created_at')
            ->limit($limit)
            ->get();

        foreach ($recentRatings as $rating) {
            $activities[] = [
                'type' => 'rating',
                'doctor' => $rating->doctor->name,
                'rating' => $rating->rating,
                'timestamp' => $rating->created_at,
            ];
        }

        // Sort by timestamp (newest first)
        usort($activities, function($a, $b) {
            return $b['timestamp']->timestamp <=> $a['timestamp']->timestamp;
        });

        return array_slice($activities, 0, $limit * 3);
    }

    /**
     * Get doctor performance report
     */
    public function getDoctorPerformanceReport(): array
    {
        $doctors = User::where('role', 'dokter')->get();

        return $doctors->map(function($doctor) {
            $totalAppointments = Appointment::where('doctor_id', $doctor->id)->count();
            $completedAppointments = Appointment::where('doctor_id', $doctor->id)
                ->where('status', 'completed')
                ->count();
            $averageRating = Rating::where('doctor_id', $doctor->id)->avg('rating');

            return [
                'id' => $doctor->id,
                'name' => $doctor->name,
                'specialization' => $doctor->specialization ?? 'General',
                'total_appointments' => $totalAppointments,
                'completed_appointments' => $completedAppointments,
                'completion_rate' => $totalAppointments > 0 
                    ? ($completedAppointments / $totalAppointments) * 100 
                    : 0,
                'average_rating' => round($averageRating ?? 0, 2),
                'total_ratings' => Rating::where('doctor_id', $doctor->id)->count(),
            ];
        })->sortByDesc('average_rating')->values()->toArray();
    }

    /**
     * Helper methods
     */

    private function getActiveUsersToday(): int
    {
        $today = now()->startOfDay();
        
        return User::where('last_activity_at', '>=', $today)->count();
    }

    private function calculateRevenue(): float
    {
        // Implementation depends on payment system
        // Placeholder: count completed consultations × average fee
        return Consultation::where('status', 'completed')->count() * 50000;
    }

    private function calculateAppointmentCompletionRate($from): float
    {
        $total = Appointment::whereBetween('created_at', [$from, now()])->count();
        $completed = Appointment::where('status', 'completed')
            ->whereBetween('created_at', [$from, now()])
            ->count();

        return $total > 0 ? ($completed / $total) * 100 : 0;
    }

    private function calculateAppointmentCancellationRate($from): float
    {
        $total = Appointment::whereBetween('created_at', [$from, now()])->count();
        $cancelled = Appointment::where('status', 'cancelled')
            ->whereBetween('created_at', [$from, now()])
            ->count();

        return $total > 0 ? ($cancelled / $total) * 100 : 0;
    }

    private function getAppointmentStatusDistribution(): array
    {
        return [
            'pending' => Appointment::where('status', 'pending')->count(),
            'confirmed' => Appointment::where('status', 'confirmed')->count(),
            'completed' => Appointment::where('status', 'completed')->count(),
            'cancelled' => Appointment::where('status', 'cancelled')->count(),
        ];
    }

    private function getAppointmentTypeDistribution(): array
    {
        return [
            'online' => Appointment::where('type', 'online')->count(),
            'offline' => Appointment::where('type', 'offline')->count(),
        ];
    }

    private function calculateUserGrowth(string $period): float
    {
        // Compare current period with previous period
        $now = now();
        
        if ($period === 'week') {
            $currentStart = $now->copy()->startOfWeek();
            $previousStart = $now->copy()->subWeek()->startOfWeek();
        } else {
            $currentStart = $now->copy()->startOfMonth();
            $previousStart = $now->copy()->subMonth()->startOfMonth();
        }

        $currentCount = User::whereBetween('created_at', [$currentStart, $now])->count();
        $previousCount = User::whereBetween('created_at', [$previousStart, $currentStart])->count();

        if ($previousCount === 0) {
            return $currentCount > 0 ? 100 : 0;
        }

        return (($currentCount - $previousCount) / $previousCount) * 100;
    }

    private function calculateAverageConsultationDuration(): float
    {
        return Consultation::whereNotNull('ended_at')
            ->whereNotNull('started_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, started_at, ended_at)) as avg_duration')
            ->value('avg_duration') ?? 0;
    }

    private function getTopDoctorsByConsultations(int $limit): array
    {
        return User::where('role', 'dokter')
            ->withCount('consultations')
            ->orderByDesc('consultations_count')
            ->limit($limit)
            ->get(['id', 'name', 'specialization'])
            ->makeHidden(['role'])
            ->toArray();
    }

    private function getTopRatedDoctors(int $limit): array
    {
        return User::where('role', 'dokter')
            ->select('id', 'name', 'specialization')
            ->selectRaw('AVG(ratings.rating) as average_rating')
            ->leftJoin('ratings', 'ratings.doctor_id', '=', 'users.id')
            ->groupBy('users.id', 'users.name', 'users.specialization')
            ->orderByDesc('average_rating')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    private function countDatabaseTables(): int
    {
        return DB::select('SELECT COUNT(*) as count FROM information_schema.TABLES WHERE TABLE_SCHEMA = ?', [config('database.connections.mysql.database')])[0]->count ?? 0;
    }

    private function getDatabaseSize(): string
    {
        $size = DB::select('SELECT SUM(data_length + index_length) as size FROM information_schema.TABLES WHERE TABLE_SCHEMA = ?', [config('database.connections.mysql.database')])[0]->size ?? 0;
        return $this->formatBytes($size);
    }

    private function checkCacheStatus(): string
    {
        try {
            cache()->put('health_check', 'ok', 60);
            return cache()->get('health_check') === 'ok' ? 'ok' : 'error';
        } catch (\Exception $e) {
            return 'error';
        }
    }

    private function getStorageUsage(): string
    {
        $size = 0;
        $path = storage_path();
        
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path)) as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }

        return $this->formatBytes($size);
    }

    private function getAvailableStorage(): string
    {
        $available = disk_free_space(storage_path());
        return $this->formatBytes($available ?? 0);
    }

    private function getStoragePercentage(): float
    {
        $total = disk_total_space(storage_path());
        $free = disk_free_space(storage_path());
        
        if ($total === false || $free === false) {
            return 0;
        }

        return (($total - $free) / $total) * 100;
    }

    private function getLastBackupTime(): ?string
    {
        // Implementation depends on backup system
        return 'Not implemented';
    }

    private function getAppointmentsTrendData(int $days): array
    {
        $data = [];
        
        for ($i = $days; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $count = Appointment::whereDate('created_at', $date)->count();
            
            $data[] = [
                'date' => $date,
                'count' => $count,
            ];
        }

        return $data;
    }

    private function getUsersTrendData(int $days): array
    {
        $data = [];
        
        for ($i = $days; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $count = User::whereDate('created_at', $date)->count();
            
            $data[] = [
                'date' => $date,
                'count' => $count,
            ];
        }

        return $data;
    }

    private function getRatingsTrendData(int $days): array
    {
        $data = [];
        
        for ($i = $days; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $average = Rating::whereDate('created_at', $date)->avg('rating');
            
            $data[] = [
                'date' => $date,
                'average' => $average ?? 0,
            ];
        }

        return $data;
    }

    private function getConsultationsTrendData(int $days): array
    {
        $data = [];
        
        for ($i = $days; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $count = Consultation::whereDate('created_at', $date)->count();
            
            $data[] = [
                'date' => $date,
                'count' => $count,
            ];
        }

        return $data;
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
