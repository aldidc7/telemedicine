<?php

namespace App\Services\Analytics;

use App\Models\Dokter;
use App\Models\Konsultasi;
use App\Models\Rating;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DoctorMetricsService
{
    const CACHE_DURATION = 120; // 2 hours for doctor metrics

    /**
     * Get metrics for a specific doctor
     */
    public function getDoctorMetrics(int $doctorId): array
    {
        $cacheKey = "doctor.metrics.$doctorId";
        
        return Cache::remember($cacheKey, self::CACHE_DURATION * 60, function () use ($doctorId) {
            $doctor = Dokter::findOrFail($doctorId);
            
            return [
                'doctor_id' => $doctorId,
                'doctor_name' => $doctor->nama_dokter,
                'specialization' => $doctor->spesialisasi,
                'consultation_count' => $this->getConsultationCount($doctorId),
                'completed_consultations' => $this->getCompletedConsultations($doctorId),
                'average_rating' => $this->getAverageRating($doctorId),
                'total_ratings' => $this->getTotalRatings($doctorId),
                'response_time' => $this->getAverageResponseTime($doctorId),
                'average_duration' => $this->getAverageDuration($doctorId),
                'total_revenue' => $this->getTotalRevenue($doctorId),
                'monthly_revenue' => $this->getMonthlyRevenue($doctorId),
                'active_patients' => $this->getActivePatients($doctorId),
                'availability_score' => $this->getAvailabilityScore($doctorId),
                'completion_rate' => $this->getCompletionRate($doctorId),
                'cancellation_rate' => $this->getCancellationRate($doctorId),
                'rating_breakdown' => $this->getRatingBreakdown($doctorId),
                'monthly_trend' => $this->getMonthlyTrend($doctorId),
            ];
        });
    }

    /**
     * Get leaderboard of top doctors by rating
     */
    public function getLeaderboard(string $sortBy = 'rating', int $limit = 10): array
    {
        $cacheKey = "doctors.leaderboard.$sortBy";
        
        return Cache::remember($cacheKey, self::CACHE_DURATION * 60, function () use ($sortBy, $limit) {
            $doctors = Dokter::select(
                'dokter.id',
                'dokter.nama_dokter',
                'dokter.spesialisasi',
                DB::raw('COALESCE(AVG(r.rating), 0) as avg_rating'),
                DB::raw('COUNT(r.id) as rating_count'),
                DB::raw('COUNT(k.id) as consultation_count'),
                DB::raw('SUM(CASE WHEN k.status = "selesai" THEN 1 ELSE 0 END) as completed'),
                DB::raw('SUM(COALESCE(p.amount, 0)) as total_revenue')
            )
            ->leftJoin('ratings as r', 'dokter.id', '=', 'r.doctor_id')
            ->leftJoin('konsultasi as k', 'dokter.id', '=', 'k.doctor_id')
            ->leftJoin('payments as p', 'k.id', '=', 'p.consultation_id')
            ->groupBy('dokter.id', 'dokter.nama_dokter', 'dokter.spesialisasi');
            
            if ($sortBy === 'rating') {
                $doctors = $doctors->orderByDesc('avg_rating');
            } elseif ($sortBy === 'revenue') {
                $doctors = $doctors->orderByDesc('total_revenue');
            } elseif ($sortBy === 'consultations') {
                $doctors = $doctors->orderByDesc('consultation_count');
            } else {
                $doctors = $doctors->orderByDesc('avg_rating');
            }
            
            return $doctors->limit($limit)
                ->get()
                ->map(function ($doctor) {
                    return [
                        'doctor_id' => $doctor->id,
                        'name' => $doctor->nama_dokter,
                        'specialization' => $doctor->spesialisasi,
                        'rating' => round($doctor->avg_rating, 2),
                        'rating_count' => $doctor->rating_count,
                        'consultations' => $doctor->consultation_count,
                        'completed' => $doctor->completed,
                        'revenue' => round($doctor->total_revenue, 2),
                    ];
                })
                ->toArray();
        });
    }

    /**
     * Get performance report for a doctor
     */
    public function getPerformanceReport(int $doctorId): array
    {
        $doctor = Dokter::findOrFail($doctorId);
        
        return [
            'doctor' => [
                'id' => $doctor->id,
                'name' => $doctor->nama_dokter,
                'specialization' => $doctor->spesialisasi,
                'image' => $doctor->foto,
            ],
            'metrics' => $this->getDoctorMetrics($doctorId),
            'comparison' => $this->getSpecializationComparison($doctor->spesialisasi, $doctorId),
            'recommendations' => $this->getRecommendations($doctorId),
        ];
    }

    /**
     * Calculate commission for a doctor
     */
    public function calculateCommission(int $doctorId, string $period = 'monthly'): array
    {
        $startDate = $period === 'monthly' 
            ? Carbon::now()->startOfMonth() 
            : Carbon::now()->startOfYear();
        
        $endDate = $period === 'monthly'
            ? Carbon::now()->endOfMonth()
            : Carbon::now()->endOfYear();
        
        $revenue = Payment::whereHas('consultation', function ($query) use ($doctorId) {
            $query->where('doctor_id', $doctorId);
        })
        ->where('status', 'completed')
        ->whereBetween('created_at', [$startDate, $endDate])
        ->sum('amount');
        
        // Commission structure: 70% to doctor, 30% to platform
        $doctorEarnings = $revenue * 0.70;
        $platformFee = $revenue * 0.30;
        
        return [
            'doctor_id' => $doctorId,
            'period' => $period,
            'total_revenue' => round($revenue, 2),
            'doctor_earnings' => round($doctorEarnings, 2),
            'platform_fee' => round($platformFee, 2),
            'commission_rate' => 70,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
        ];
    }

    /**
     * Get detailed ratings for a doctor
     */
    public function getDetailedRatings(int $doctorId, int $limit = 20): array
    {
        $ratings = Rating::where('doctor_id', $doctorId)
            ->with('user:id,nama_lengkap')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(function ($rating) {
                return [
                    'id' => $rating->id,
                    'rating' => $rating->rating,
                    'comment' => $rating->comment,
                    'user' => $rating->user->nama_lengkap ?? 'Anonymous',
                    'created_at' => $rating->created_at->toDateString(),
                ];
            });
        
        return [
            'doctor_id' => $doctorId,
            'ratings' => $ratings->toArray(),
            'total' => Rating::where('doctor_id', $doctorId)->count(),
            'average' => round(Rating::where('doctor_id', $doctorId)->avg('rating'), 2),
        ];
    }

    /**
     * Get revenue breakdown for a doctor
     */
    public function getRevenueBreakdown(int $doctorId, string $period = 'monthly'): array
    {
        $startDate = $period === 'monthly'
            ? Carbon::now()->startOfMonth()
            : Carbon::now()->startOfYear();
        
        $payments = Payment::whereHas('consultation', function ($query) use ($doctorId) {
            $query->where('doctor_id', $doctorId);
        })
        ->where('status', 'completed')
        ->where('created_at', '>=', $startDate)
        ->selectRaw('metode_pembayaran, COUNT(*) as transaction_count, SUM(amount) as total')
        ->groupBy('metode_pembayaran')
        ->get();
        
        return [
            'doctor_id' => $doctorId,
            'period' => $period,
            'by_method' => $payments->map(function ($payment) {
                return [
                    'method' => $payment->metode_pembayaran,
                    'transactions' => $payment->transaction_count,
                    'total' => round($payment->total, 2),
                ];
            })->toArray(),
            'total_revenue' => round($payments->sum('total'), 2),
        ];
    }

    /**
     * Clear doctor metrics cache
     */
    public function clearCache(int $doctorId = null): void
    {
        if ($doctorId) {
            Cache::forget("doctor.metrics.$doctorId");
        } else {
            // Clear all doctor metrics
            $doctors = Dokter::pluck('id');
            foreach ($doctors as $id) {
                Cache::forget("doctor.metrics.$id");
            }
        }
        
        Cache::forget('doctors.leaderboard.rating');
        Cache::forget('doctors.leaderboard.revenue');
        Cache::forget('doctors.leaderboard.consultations');
    }

    // ===== Private Helper Methods =====

    private function getConsultationCount(int $doctorId): int
    {
        return Konsultasi::where('doctor_id', $doctorId)->count();
    }

    private function getCompletedConsultations(int $doctorId): int
    {
        return Konsultasi::where('doctor_id', $doctorId)
            ->where('status', 'selesai')
            ->count();
    }

    private function getAverageRating(int $doctorId): float
    {
        return (float) (Rating::where('doctor_id', $doctorId)->avg('rating') ?? 0);
    }

    private function getTotalRatings(int $doctorId): int
    {
        return Rating::where('doctor_id', $doctorId)->count();
    }

    private function getAverageResponseTime(int $doctorId): int
    {
        // Response time in minutes (time to first message after consultation starts)
        $avgMinutes = Konsultasi::where('doctor_id', $doctorId)
            ->selectRaw('AVG(EXTRACT(EPOCH FROM (first_response_at - created_at))/60) as avg_time')
            ->where('first_response_at', '!=', null)
            ->value('avg_time');
        
        return (int) ($avgMinutes ?? 0);
    }

    private function getAverageDuration(int $doctorId): int
    {
        // Average consultation duration in minutes
        $avgMinutes = Konsultasi::where('doctor_id', $doctorId)
            ->selectRaw('AVG(EXTRACT(EPOCH FROM (ended_at - started_at))/60) as avg_duration')
            ->where('status', 'selesai')
            ->where('started_at', '!=', null)
            ->where('ended_at', '!=', null)
            ->value('avg_duration');
        
        return (int) ($avgMinutes ?? 0);
    }

    private function getTotalRevenue(int $doctorId): float
    {
        return (float) Payment::whereHas('consultation', function ($query) use ($doctorId) {
            $query->where('doctor_id', $doctorId);
        })
        ->where('status', 'completed')
        ->sum('amount');
    }

    private function getMonthlyRevenue(int $doctorId): float
    {
        return (float) Payment::whereHas('consultation', function ($query) use ($doctorId) {
            $query->where('doctor_id', $doctorId);
        })
        ->where('status', 'completed')
        ->where('created_at', '>=', Carbon::now()->startOfMonth())
        ->sum('amount');
    }

    private function getActivePatients(int $doctorId): int
    {
        return Konsultasi::where('doctor_id', $doctorId)
            ->where('created_at', '>=', Carbon::now()->subMonths(6))
            ->distinct('patient_id')
            ->count('patient_id');
    }

    private function getAvailabilityScore(int $doctorId): int
    {
        $doctor = Dokter::find($doctorId);
        if (!$doctor) {
            return 0;
        }
        
        $status = $doctor->status;
        
        return match ($status) {
            'available' => 100,
            'busy' => 50,
            'offline' => 0,
            default => 25,
        };
    }

    private function getCompletionRate(int $doctorId): float
    {
        $total = Konsultasi::where('doctor_id', $doctorId)->count();
        if ($total === 0) {
            return 0;
        }
        
        $completed = Konsultasi::where('doctor_id', $doctorId)
            ->where('status', 'selesai')
            ->count();
        
        return round(($completed / $total) * 100, 2);
    }

    private function getCancellationRate(int $doctorId): float
    {
        $total = Konsultasi::where('doctor_id', $doctorId)->count();
        if ($total === 0) {
            return 0;
        }
        
        $cancelled = Konsultasi::where('doctor_id', $doctorId)
            ->where('status', 'batal')
            ->count();
        
        return round(($cancelled / $total) * 100, 2);
    }

    private function getRatingBreakdown(int $doctorId): array
    {
        $breakdown = Rating::where('doctor_id', $doctorId)
            ->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->get()
            ->pluck('count', 'rating')
            ->toArray();
        
        return [
            '5_stars' => $breakdown[5] ?? 0,
            '4_stars' => $breakdown[4] ?? 0,
            '3_stars' => $breakdown[3] ?? 0,
            '2_stars' => $breakdown[2] ?? 0,
            '1_star' => $breakdown[1] ?? 0,
        ];
    }

    private function getMonthlyTrend(int $doctorId): array
    {
        $trend = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $count = Konsultasi::where('doctor_id', $doctorId)
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
            
            $trend[$month->format('Y-m')] = $count;
        }
        
        return $trend;
    }

    private function getSpecializationComparison(string $specialization, int $doctorId): array
    {
        // Compare doctor metrics with average for their specialization
        $avgRating = Rating::whereHas('doctor', function ($query) use ($specialization) {
            $query->where('spesialisasi', $specialization);
        })->avg('rating');
        
        $avgConsultations = Konsultasi::whereHas('doctor', function ($query) use ($specialization) {
            $query->where('spesialisasi', $specialization);
        })->count() / max(Dokter::where('spesialisasi', $specialization)->count(), 1);
        
        $doctorRating = $this->getAverageRating($doctorId);
        $doctorConsultations = $this->getConsultationCount($doctorId);
        
        return [
            'specialization' => $specialization,
            'doctor_rating' => round($doctorRating, 2),
            'specialization_avg_rating' => round($avgRating, 2),
            'rating_percentile' => round(($doctorRating / max($avgRating, 1)) * 100, 2),
            'doctor_consultations' => $doctorConsultations,
            'specialization_avg_consultations' => round($avgConsultations, 0),
        ];
    }

    private function getRecommendations(int $doctorId): array
    {
        $recommendations = [];
        
        $completionRate = $this->getCompletionRate($doctorId);
        if ($completionRate < 80) {
            $recommendations[] = [
                'type' => 'warning',
                'message' => 'Improve completion rate (currently ' . $completionRate . '%)',
            ];
        }
        
        $avgRating = $this->getAverageRating($doctorId);
        if ($avgRating < 3.5) {
            $recommendations[] = [
                'type' => 'warning',
                'message' => 'Patient satisfaction is below average (rating: ' . round($avgRating, 2) . ')',
            ];
        }
        
        if ($this->getTotalRatings($doctorId) < 10) {
            $recommendations[] = [
                'type' => 'info',
                'message' => 'Encourage patients to leave reviews',
            ];
        }
        
        if (count($recommendations) === 0) {
            $recommendations[] = [
                'type' => 'success',
                'message' => 'All metrics are performing well!',
            ];
        }
        
        return $recommendations;
    }
}
