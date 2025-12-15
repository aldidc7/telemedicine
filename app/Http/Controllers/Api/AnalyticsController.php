<?php

namespace App\Http\Controllers\Api;

use App\Services\AnalyticsService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AnalyticsController extends BaseApiController
{
    protected AnalyticsService $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Get dashboard overview
     * 
     * @OA\Get(
     *     path="/api/v1/analytics/overview",
     *     summary="Get Analytics Dashboard Overview",
     *     description="Retrieve real-time overview of all analytics metrics",
     *     tags={"Analytics"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Analytics overview retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             properties={
     *                 @OA\Property(property="success", type="boolean"),
     *                 @OA\Property(property="data", type="object"),
     *                 @OA\Property(property="timestamp", type="string", format="date-time")
     *             }
     *         )
     *     )
     * )
     */
    public function getDashboardOverview()
    {
        try {
            $data = $this->analyticsService->getDashboardOverview();

            return $this->apiResponse($data, 'Analytics overview retrieved successfully');
        } catch (\Exception $e) {
            return $this->apiError('Failed to retrieve analytics', null, 500);
        }
    }

    /**
     * Get consultation metrics
     * 
     * @OA\Get(
     *     path="/api/v1/analytics/consultations",
     *     summary="Get Consultation Metrics",
     *     description="Get real-time consultation metrics for specified period",
     *     tags={"Analytics"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="period",
     *         in="query",
     *         description="Period: today, week, month",
     *         required=false,
     *         @OA\Schema(type="string", enum={"today", "week", "month"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Consultation metrics retrieved successfully"
     *     )
     * )
     */
    public function getConsultationMetrics(Request $request)
    {
        try {
            $period = $request->query('period', 'today');
            $data = $this->analyticsService->getConsultationMetrics($period);

            return $this->apiResponse($data, 'Consultation metrics retrieved successfully');
        } catch (\Exception $e) {
            return $this->apiError('Failed to retrieve consultation metrics', null, 500);
        }
    }

    /**
     * Get doctor performance analytics
     * 
     * @OA\Get(
     *     path="/api/v1/analytics/doctors",
     *     summary="Get Doctor Performance Analytics",
     *     description="Get performance metrics for all doctors",
     *     tags={"Analytics"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Number of doctors to retrieve",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Doctor performance retrieved successfully"
     *     )
     * )
     */
    public function getDoctorPerformance(Request $request)
    {
        try {
            $limit = $request->query('limit', 10);
            $data = $this->analyticsService->getDoctorPerformance($limit);

            return $this->apiResponse($data, 'Doctor performance retrieved successfully');
        } catch (\Exception $e) {
            return $this->apiError('Failed to retrieve doctor performance', null, 500);
        }
    }

    /**
     * Get patient health trends
     * 
     * @OA\Get(
     *     path="/api/v1/analytics/health-trends",
     *     summary="Get Patient Health Trends",
     *     description="Analyze patient health trends and demographics",
     *     tags={"Analytics"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Health trends retrieved successfully"
     *     )
     * )
     */
    public function getPatientHealthTrends()
    {
        try {
            $data = $this->analyticsService->getPatientHealthTrends();

            return $this->apiResponse($data, 'Patient health trends retrieved successfully');
        } catch (\Exception $e) {
            return $this->apiError('Failed to retrieve health trends', null, 500);
        }
    }

    /**
     * Get revenue analytics
     * 
     * @OA\Get(
     *     path="/api/v1/analytics/revenue",
     *     summary="Get Revenue Analytics",
     *     description="Analyze revenue metrics and doctor earnings",
     *     tags={"Analytics"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="period",
     *         in="query",
     *         description="Period: today, week, month, year",
     *         required=false,
     *         @OA\Schema(type="string", enum={"today", "week", "month", "year"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Revenue analytics retrieved successfully"
     *     )
     * )
     */
    public function getRevenueAnalytics(Request $request)
    {
        try {
            $period = $request->query('period', 'month');
            $data = $this->analyticsService->getRevenueAnalytics($period);

            return $this->apiResponse($data, 'Revenue analytics retrieved successfully');
        } catch (\Exception $e) {
            return $this->apiError('Failed to retrieve revenue analytics', null, 500);
        }
    }

    /**
     * Get analytics by date range
     * 
     * @OA\Get(
     *     path="/api/v1/analytics/range",
     *     summary="Get Analytics by Date Range",
     *     description="Get detailed analytics for a specific date range",
     *     tags={"Analytics"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Start date (Y-m-d)",
     *         required=true,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="End date (Y-m-d)",
     *         required=true,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="metrics",
     *         in="query",
     *         description="Comma-separated metrics",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Analytics retrieved successfully"
     *     )
     * )
     */
    public function getAnalyticsByDateRange(Request $request)
    {
        try {
            $request->validate([
                'start_date' => 'required|date_format:Y-m-d',
                'end_date' => 'required|date_format:Y-m-d|after_or_equal:start_date',
            ]);

            $startDate = Carbon::createFromFormat('Y-m-d', $request->start_date)->startOfDay();
            $endDate = Carbon::createFromFormat('Y-m-d', $request->end_date)->endOfDay();
            $metrics = $request->query('metrics') ? explode(',', $request->query('metrics')) : ['consultations', 'revenue'];

            $data = $this->analyticsService->getAnalyticsByDateRange($startDate, $endDate, $metrics);

            return $this->apiResponse($data, 'Analytics retrieved successfully');
        } catch (\Exception $e) {
            return $this->apiError('Failed to retrieve analytics: ' . $e->getMessage(), null, 422);
        }
    }

    /**
     * Refresh analytics cache
     * 
     * @OA\Post(
     *     path="/api/v1/analytics/refresh",
     *     summary="Refresh Analytics Cache",
     *     description="Force refresh of all cached analytics data",
     *     tags={"Analytics"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Cache refreshed successfully"
     *     )
     * )
     */
    public function refreshCache()
    {
        try {
            $this->analyticsService->clearCache();

            return $this->apiResponse(null, 'Analytics cache refreshed successfully');
        } catch (\Exception $e) {
            return $this->apiError('Failed to refresh cache', null, 500);
        }
    }

    /**
     * Get cache statistics and status
     * 
     * @OA\Get(
     *     path="/api/v1/analytics/cache/status",
     *     summary="Get Cache Status",
     *     description="Retrieve cache statistics and freshness",
     *     tags={"Analytics"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Cache status retrieved",
     *         @OA\JsonContent(
     *             type="object",
     *             properties={
     *                 "cache_size": {"type": "string"},
     *                 "total_keys": {"type": "integer"},
     *                 "last_refresh": {"type": "string", "format": "date-time"},
     *                 "hit_rate": {"type": "number"}
     *             }
     *         )
     *     )
     * )
     */
    public function getCacheStatus()
    {
        try {
            $stats = $this->analyticsService->getCacheStats();

            return $this->apiResponse($stats, 'Cache status retrieved successfully');
        } catch (\Exception $e) {
            return $this->apiError('Failed to get cache status', null, 500);
        }
    }

    /**
     * Warm analytics cache
     * 
     * @OA\Post(
     *     path="/api/v1/analytics/cache/warm",
     *     summary="Warm Analytics Cache",
     *     description="Pre-load commonly accessed analytics data into cache",
     *     tags={"Analytics"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Cache warmed successfully"
     *     )
     * )
     */
    public function warmCache()
    {
        try {
            $this->analyticsService->warmCache();

            return $this->apiResponse(null, 'Cache warmed successfully');
        } catch (\Exception $e) {
            return $this->apiError('Failed to warm cache', null, 500);
        }
    }

    /**
     * Get real-time metrics update
     * 
     * @OA\Get(
     *     path="/api/v1/analytics/realtime",
     *     summary="Get Real-time Metrics",
     *     description="Retrieve fresh analytics data bypassing cache",
     *     tags={"Analytics"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Real-time metrics retrieved"
     *     )
     * )
     */
    public function getRealtimeMetrics()
    {
        try {
            // Clear cache to force fresh data
            $this->analyticsService->clearCache();
            
            // Get fresh overview
            $data = $this->analyticsService->getDashboardOverview();

            return $this->apiResponse([
                'timestamp' => now(),
                'data' => $data,
                'cache_status' => 'bypassed',
            ], 'Real-time metrics retrieved successfully');
        } catch (\Exception $e) {
            return $this->apiError('Failed to get real-time metrics', null, 500);
        }
    }

    /**
     * Get top-rated doctors
     */
    public function getTopRatedDoctors(Request $request)
    {
        try {
            $limit = $request->query('limit', 10);
            $data = $this->analyticsService->getTopRatedDoctors($limit);

            return $this->apiResponse($data, 'Top-rated doctors retrieved successfully');
        } catch (\Exception $e) {
            return $this->apiError('Failed to retrieve top-rated doctors', null, 500);
        }
    }

    /**
     * Get most active doctors
     */
    public function getMostActiveDoctors(Request $request)
    {
        try {
            $limit = $request->query('limit', 10);
            $data = $this->analyticsService->getMostActiveDoctors($limit);

            return $this->apiResponse($data, 'Most active doctors retrieved successfully');
        } catch (\Exception $e) {
            return $this->apiError('Failed to retrieve most active doctors', null, 500);
        }
    }

    /**
     * Get patient demographics
     */
    public function getPatientDemographics()
    {
        try {
            $data = $this->analyticsService->getPatientDemographics();

            return $this->apiResponse($data, 'Patient demographics retrieved successfully');
        } catch (\Exception $e) {
            return $this->apiError('Failed to retrieve patient demographics', null, 500);
        }
    }

    /**
     * Get engagement metrics
     */
    public function getEngagementMetrics(Request $request)
    {
        try {
            $period = $request->query('period', 'month');
            $data = $this->analyticsService->getEngagementMetrics($period);

            return $this->apiResponse($data, 'Engagement metrics retrieved successfully');
        } catch (\Exception $e) {
            return $this->apiError('Failed to retrieve engagement metrics', null, 500);
        }
    }

    /**
     * Get specialization distribution
     */
    public function getSpecializationDistribution()
    {
        try {
            $data = $this->analyticsService->getSpecializationDistribution();

            return $this->apiResponse($data, 'Specialization distribution retrieved successfully');
        } catch (\Exception $e) {
            return $this->apiError('Failed to retrieve specialization distribution', null, 500);
        }
    }

    /**
     * Get consultation trends
     */
    public function getConsultationTrends(Request $request)
    {
        try {
            $startDate = $request->query('start_date', Carbon::now()->subDays(30)->toDateString());
            $endDate = $request->query('end_date', now()->toDateString());
            
            $data = $this->analyticsService->getConsultationTrendsByDate($startDate, $endDate);

            return $this->apiResponse($data, 'Consultation trends retrieved successfully');
        } catch (\Exception $e) {
            return $this->apiError('Failed to retrieve consultation trends', null, 500);
        }
    }

    /**
     * Get user registration trends
     */
    public function getUserTrends(Request $request)
    {
        try {
            $startDate = $request->query('start_date', Carbon::now()->subDays(30)->toDateString());
            $endDate = $request->query('end_date', now()->toDateString());
            
            $data = $this->analyticsService->getUserTrendsByDate($startDate, $endDate);

            return $this->apiResponse($data, 'User registration trends retrieved successfully');
        } catch (\Exception $e) {
            return $this->apiError('Failed to retrieve user trends', null, 500);
        }
    }

    /**
     * Get growth metrics
     */
    public function getGrowthMetrics()
    {
        try {
            $data = $this->analyticsService->getGrowthMetrics();

            return $this->apiResponse($data, 'Growth metrics retrieved successfully');
        } catch (\Exception $e) {
            return $this->apiError('Failed to retrieve growth metrics', null, 500);
        }
    }

    /**
     * Get user retention metrics
     */
    public function getUserRetention()
    {
        try {
            $data = $this->analyticsService->getUserRetention();

            return $this->apiResponse($data, 'User retention metrics retrieved successfully');
        } catch (\Exception $e) {
            return $this->apiError('Failed to retrieve user retention metrics', null, 500);
        }
    }
}
