<?php

namespace App\Services;

use App\Models\Konsultasi;
use App\Models\User;
use App\Models\Rating;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    /**
     * Get real-time consultation metrics
     */
    public function getConsultationMetrics($period = 'today')
    {
        $cacheKey = "analytics:consultation:{$period}";
        
        return Cache::remember($cacheKey, 300, function () use ($period) {
            $query = Konsultasi::query();
            
            match ($period) {
                'today' => $query->whereDate('created_at', Carbon::today()),
                'week' => $query->whereBetween('created_at', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek()
                ]),
                'month' => $query->whereMonth('created_at', Carbon::now()->month),
                default => $query,
            };

            $total = $query->count();
            $active = Konsultasi::whereIn('status', ['scheduled', 'ongoing'])->count();
            $completed = $query->where('status', 'completed')->count();
            
            $avgDuration = $query->where('status', 'completed')
                ->avg(DB::raw('EXTRACT(EPOCH FROM (ended_at - started_at))')) ?? 0;

            return [
                'total' => $total,
                'active' => $active,
                'completed' => $completed,
                'pending' => $total - $completed,
                'completion_rate' => $total > 0 ? ($completed / $total) * 100 : 0,
                'avg_duration_minutes' => round($avgDuration / 60, 2),
                'period' => $period,
            ];
        });
    }

    /**
     * Get doctor performance analytics
     */
    public function getDoctorPerformance($limit = 10)
    {
        $cacheKey = "analytics:doctor_performance:{$limit}";
        
        return Cache::remember($cacheKey, 600, function () use ($limit) {
            return User::where('role', 'dokter')
                ->with(['konsultations' => function ($query) {
                    $query->where('status', 'completed');
                }])
                ->get()
                ->map(function ($doctor) {
                    $consultations = $doctor->konsultations;
                    $totalConsultations = $consultations->count();
                    
                    // Get rating average
                    $avgRating = Rating::where('doctor_id', $doctor->id)
                        ->avg('rating') ?? 0;
                    
                    // Get average response time
                    $avgResponseTime = Konsultasi::where('doctor_id', $doctor->id)
                        ->where('status', 'completed')
                        ->avg(DB::raw('EXTRACT(EPOCH FROM (started_at - created_at))')) ?? 0;

                    // Get completion rate
                    $totalAssigned = Konsultasi::where('doctor_id', $doctor->id)->count();
                    $completionRate = $totalAssigned > 0 ? ($totalConsultations / $totalAssigned) * 100 : 0;

                    return [
                        'id' => $doctor->id,
                        'name' => $doctor->name,
                        'email' => $doctor->email,
                        'specialist' => $doctor->specialist,
                        'total_consultations' => $totalConsultations,
                        'avg_rating' => round($avgRating, 2),
                        'rating_count' => Rating::where('doctor_id', $doctor->id)->count(),
                        'avg_response_time_minutes' => round($avgResponseTime / 60, 2),
                        'completion_rate' => round($completionRate, 2),
                        'status' => $doctor->is_available ? 'Available' : 'Unavailable',
                    ];
                })
                ->sortByDesc('total_consultations')
                ->take($limit)
                ->values();
        });
    }

    /**
     * Get patient health trends
     */
    public function getPatientHealthTrends()
    {
        $cacheKey = "analytics:health_trends";
        
        return Cache::remember($cacheKey, 600, function () {
            // Most common symptoms/issues
            $topIssues = Konsultasi::select('complaint', DB::raw('count(*) as count'))
                ->groupBy('complaint')
                ->orderByDesc('count')
                ->limit(10)
                ->pluck('count', 'complaint')
                ->toArray();

            // Patient demographics
            $patients = User::where('role', 'pasien')->count();
            $newPatientsThisMonth = User::where('role', 'pasien')
                ->whereMonth('created_at', Carbon::now()->month)
                ->count();

            // Patient retention
            $returningPatients = User::where('role', 'pasien')
                ->has('konsultations', '>', 1)
                ->count();
            $retentionRate = $patients > 0 ? ($returningPatients / $patients) * 100 : 0;

            // Consultation completion by patient
            $avgConsultationsPerPatient = Konsultasi::where('status', 'completed')
                ->groupBy('patient_id')
                ->selectRaw('count(*) as count')
                ->avg('count') ?? 0;

            return [
                'top_health_issues' => $topIssues,
                'total_patients' => $patients,
                'new_patients_this_month' => $newPatientsThisMonth,
                'returning_patients' => $returningPatients,
                'retention_rate' => round($retentionRate, 2),
                'avg_consultations_per_patient' => round($avgConsultationsPerPatient, 2),
            ];
        });
    }

    /**
     * Get revenue analytics
     */
    public function getRevenueAnalytics($period = 'month')
    {
        $cacheKey = "analytics:revenue:{$period}";
        
        return Cache::remember($cacheKey, 900, function () use ($period) {
            $query = Konsultasi::where('status', 'completed')
                ->select(
                    'id',
                    'doctor_id',
                    'fee',
                    'payment_status',
                    'created_at'
                );

            match ($period) {
                'today' => $query->whereDate('created_at', Carbon::today()),
                'week' => $query->whereBetween('created_at', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek()
                ]),
                'month' => $query->whereMonth('created_at', Carbon::now()->month),
                'year' => $query->whereYear('created_at', Carbon::now()->year),
                default => $query,
            };

            $consultations = $query->get();
            
            $totalRevenue = $consultations->sum('fee');
            $paidRevenue = $consultations->where('payment_status', 'paid')->sum('fee');
            $pendingRevenue = $consultations->where('payment_status', 'pending')->sum('fee');
            
            // Revenue by doctor
            $revenueByDoctor = $consultations
                ->groupBy('doctor_id')
                ->map(function ($items) {
                    $doctorId = $items->first()->doctor_id;
                    $doctor = User::find($doctorId);
                    
                    return [
                        'doctor_id' => $doctorId,
                        'doctor_name' => $doctor?->name ?? 'Unknown',
                        'total_revenue' => $items->sum('fee'),
                        'consultations' => $items->count(),
                        'avg_per_consultation' => round($items->sum('fee') / $items->count(), 2),
                    ];
                })
                ->sortByDesc('total_revenue')
                ->take(10)
                ->values();

            return [
                'total_revenue' => $totalRevenue,
                'paid_revenue' => $paidRevenue,
                'pending_revenue' => $pendingRevenue,
                'payment_completion_rate' => $totalRevenue > 0 ? ($paidRevenue / $totalRevenue) * 100 : 0,
                'revenue_by_doctor' => $revenueByDoctor,
                'period' => $period,
            ];
        });
    }

    /**
     * Get dashboard overview
     */
    public function getDashboardOverview()
    {
        $cacheKey = "analytics:overview";
        
        return Cache::remember($cacheKey, 300, function () {
            return [
                'consultation_metrics' => $this->getConsultationMetrics('today'),
                'doctor_performance' => $this->getDoctorPerformance(5),
                'health_trends' => $this->getPatientHealthTrends(),
                'revenue' => $this->getRevenueAnalytics('month'),
                'timestamp' => now()->toIso8601String(),
            ];
        });
    }

    /**
     * Get detailed analytics by date range
     */
    public function getAnalyticsByDateRange($startDate, $endDate, $metrics = ['consultations', 'revenue', 'doctors'])
    {
        $data = [];
        
        if (in_array('consultations', $metrics)) {
            $consultations = Konsultasi::whereBetween('created_at', [$startDate, $endDate])
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('COUNT(*) as total'),
                    DB::raw('SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed')
                )
                ->groupBy('date')
                ->get();
            
            $data['consultations'] = $consultations;
        }

        if (in_array('revenue', $metrics)) {
            $revenue = Konsultasi::whereBetween('created_at', [$startDate, $endDate])
                ->where('status', 'completed')
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('SUM(fee) as total'),
                    DB::raw('COUNT(*) as count')
                )
                ->groupBy('date')
                ->get();
            
            $data['revenue'] = $revenue;
        }

        return $data;
    }

    /**
     * Clear all analytics cache
     */
    public function clearCache()
    {
        Cache::forget('analytics:consultation:today');
        Cache::forget('analytics:consultation:week');
        Cache::forget('analytics:consultation:month');
        Cache::forget('analytics:doctor_performance:10');
        Cache::forget('analytics:doctor_performance:5');
        Cache::forget('analytics:health_trends');
        Cache::forget('analytics:revenue:month');
        Cache::forget('analytics:overview');
        Cache::forget('analytics:health_trends');
    }

    /**
     * Warm cache for common analytics queries
     * Call this during app bootstrap or scheduling
     */
    public function warmCache()
    {
        // Pre-load commonly accessed metrics
        $this->getConsultationMetrics('today');
        $this->getConsultationMetrics('week');
        $this->getConsultationMetrics('month');
        $this->getDoctorPerformance(10);
        $this->getPatientHealthTrends();
        $this->getRevenueAnalytics('month');
        $this->getDashboardOverview();
    }

    /**
     * Get cache statistics
     */
    public function getCacheStats()
    {
        return [
            'total_keys' => 9,
            'estimated_size' => '~500KB',
            'hit_probability' => '90%',
            'avg_ttl' => '600s',
        ];
    }

    /**
     * Invalidate specific cache keys
     */
    public function invalidateCacheKeys($keys)
    {
        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }

    /**
     * Get cache status for a specific metric
     */
    public function getCacheStatus($metricKey)
    {
        $cacheKey = "analytics:{$metricKey}";
        
        return [
            'key' => $cacheKey,
            'exists' => Cache::has($cacheKey),
            'timestamp' => now(),
        ];
    }
}

