<?php

namespace App\Http\Controllers\Api\Analytics;

use App\Http\Controllers\Api\ApiController;
use App\Services\Analytics\DoctorMetricsService;
use App\Models\Dokter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DoctorAnalyticsController extends ApiController
{
    protected $metricsService;

    public function __construct(DoctorMetricsService $metricsService)
    {
        $this->metricsService = $metricsService;
    }

    /**
     * Get analytics for a specific doctor
     * GET /api/v1/doctors/{id}/analytics
     */
    public function getDoctorMetrics($doctorId): JsonResponse
    {
        // Check authorization - admin or the doctor themselves
        if (Auth::user()->role !== 'admin' && Auth::user()->id !== Dokter::find($doctorId)->user_id) {
            return $this->error('Unauthorized', [], 403);
        }

        try {
            $metrics = $this->metricsService->getDoctorMetrics($doctorId);
            
            return $this->success($metrics, 'Doctor metrics retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('Doctor not found', [], 404);
        }
    }

    /**
     * Get detailed ratings for a doctor
     * GET /api/v1/doctors/{id}/ratings
     */
    public function getDoctorRatings($doctorId, Request $request): JsonResponse
    {
        if (Auth::user()->role !== 'admin' && Auth::user()->id !== Dokter::find($doctorId)->user_id) {
            return $this->error('Unauthorized', [], 403);
        }

        $limit = $request->query('limit', 20);
        $ratings = $this->metricsService->getDetailedRatings($doctorId, $limit);

        return $this->success($ratings, 'Ratings retrieved successfully');
    }

    /**
     * Get revenue breakdown for a doctor
     * GET /api/v1/doctors/{id}/revenue
     */
    public function getDoctorRevenue($doctorId, Request $request): JsonResponse
    {
        if (Auth::user()->role !== 'admin' && Auth::user()->id !== Dokter::find($doctorId)->user_id) {
            return $this->error('Unauthorized', [], 403);
        }

        $period = $request->query('period', 'monthly');
        $revenue = $this->metricsService->getRevenueBreakdown($doctorId, $period);

        return $this->success($revenue, 'Revenue breakdown retrieved successfully');
    }

    /**
     * Get doctor leaderboard
     * GET /api/v1/doctors/leaderboard
     */
    public function getLeaderboard(Request $request): JsonResponse
    {
        $sortBy = $request->query('sort_by', 'rating');
        $limit = $request->query('limit', 10);

        $leaderboard = $this->metricsService->getLeaderboard($sortBy, $limit);

        return $this->success([
            'leaderboard' => $leaderboard,
            'sort_by' => $sortBy,
            'limit' => $limit,
        ], 'Leaderboard retrieved successfully');
    }

    /**
     * Get doctor performance report
     * GET /api/v1/doctors/{id}/performance-report
     */
    public function getPerformanceReport($doctorId): JsonResponse
    {
        if (Auth::user()->role !== 'admin' && Auth::user()->id !== Dokter::find($doctorId)->user_id) {
            return $this->error('Unauthorized', [], 403);
        }

        try {
            $report = $this->metricsService->getPerformanceReport($doctorId);
            
            return $this->success($report, 'Performance report retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('Doctor not found', [], 404);
        }
    }

    /**
     * Calculate doctor commission
     * GET /api/v1/doctors/{id}/commission/calculate
     */
    public function calculateCommission($doctorId, Request $request): JsonResponse
    {
        // Only admin or the doctor themselves can view commissions
        $userDoc = Dokter::where('user_id', Auth::user()->id)->first();
        
        if (Auth::user()->role !== 'admin' && (!$userDoc || $userDoc->id !== (int) $doctorId)) {
            return $this->error('Unauthorized', [], 403);
        }

        $period = $request->query('period', 'monthly');
        $commission = $this->metricsService->calculateCommission($doctorId, $period);

        return $this->success($commission, 'Commission calculated successfully');
    }

    /**
     * Clear doctor metrics cache
     * POST /api/v1/doctors/{id}/clear-cache
     */
    public function clearMetricsCache($doctorId = null): JsonResponse
    {
        if (Auth::user()->role !== 'admin') {
            return $this->error('Unauthorized', [], 403);
        }

        if ($doctorId) {
            $this->metricsService->clearCache($doctorId);
            return $this->success([], "Doctor metrics cache cleared successfully");
        } else {
            $this->metricsService->clearCache();
            return $this->success([], "All doctor metrics cache cleared successfully");
        }
    }
}
