<?php

namespace App\Http\Controllers\Api\Analytics;

use App\Http\Controllers\Api\ApiController;
use App\Services\Analytics\AnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnalyticsController extends ApiController
{
    protected $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Get dashboard metrics
     * GET /api/v1/analytics/dashboard
     */
    public function dashboard(): JsonResponse
    {
        // Check authorization - admin only
        if (Auth::user()->role !== 'admin') {
            return $this->error('Unauthorized', [], 403);
        }

        $metrics = $this->analyticsService->getDashboardMetrics();

        return $this->success([
            'metrics' => $metrics,
            'timestamp' => now(),
        ], 'Dashboard metrics retrieved successfully');
    }

    /**
     * Get key metrics
     * GET /api/v1/analytics/metrics
     */
    public function metrics(Request $request): JsonResponse
    {
        if (Auth::user()->role !== 'admin') {
            return $this->error('Unauthorized', [], 403);
        }

        $period = $request->query('period', 'monthly');
        $metrics = $this->analyticsService->getKeyMetrics($period);

        return $this->success($metrics, 'Key metrics retrieved successfully');
    }

    /**
     * Get user activity trends
     * GET /api/v1/analytics/users/activity
     */
    public function userActivity(Request $request): JsonResponse
    {
        if (Auth::user()->role !== 'admin') {
            return $this->error('Unauthorized', [], 403);
        }

        $days = $request->query('days', '30');
        $trends = $this->analyticsService->getUserActivityTrends($days);

        return $this->success($trends, 'User activity trends retrieved successfully');
    }

    /**
     * Get consultation statistics
     * GET /api/v1/analytics/consultations
     */
    public function consultations(Request $request): JsonResponse
    {
        if (Auth::user()->role !== 'admin') {
            return $this->error('Unauthorized', [], 403);
        }

        $stats = $this->analyticsService->getConsultationStats();

        return $this->success($stats, 'Consultation statistics retrieved successfully');
    }

    /**
     * Get payment metrics
     * GET /api/v1/analytics/payments
     */
    public function payments(Request $request): JsonResponse
    {
        if (Auth::user()->role !== 'admin') {
            return $this->error('Unauthorized', [], 403);
        }

        $metrics = $this->analyticsService->getPaymentMetrics();

        return $this->success($metrics, 'Payment metrics retrieved successfully');
    }

    /**
     * Export analytics data
     * POST /api/v1/analytics/export
     */
    public function export(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            return $this->error('Unauthorized', [], 403);
        }

        $validated = $request->validate([
            'type' => 'required|in:dashboard,metrics,users,consultations,payments',
            'format' => 'required|in:csv,json',
        ]);

        $type = $validated['type'];
        $format = $validated['format'];

        $metrics = match ($type) {
            'dashboard' => $this->analyticsService->getDashboardMetrics(),
            'metrics' => $this->analyticsService->getKeyMetrics(),
            'users' => $this->analyticsService->getUserActivityTrends(),
            'consultations' => $this->analyticsService->getConsultationStats(),
            'payments' => $this->analyticsService->getPaymentMetrics(),
        };

        if ($format === 'csv') {
            $csv = $this->analyticsService->exportToCSV($metrics);
            
            return response()
                ->streamDownload(
                    function () use ($csv) {
                        echo $csv;
                    },
                    "analytics-{$type}-" . now()->toDateString() . '.csv',
                    ['Content-Type' => 'text/csv']
                );
        }

        return $this->success($metrics, 'Analytics data exported successfully');
    }

    /**
     * List saved reports
     * GET /api/v1/analytics/reports
     */
    public function listReports(): JsonResponse
    {
        if (Auth::user()->role !== 'admin') {
            return $this->error('Unauthorized', [], 403);
        }

        // TODO: Implement report listing from database
        $reports = [];

        return $this->success([
            'reports' => $reports,
            'total' => count($reports),
        ], 'Reports retrieved successfully');
    }

    /**
     * Create new report
     * POST /api/v1/analytics/reports
     */
    public function createReport(Request $request): JsonResponse
    {
        if (Auth::user()->role !== 'admin') {
            return $this->error('Unauthorized', [], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:dashboard,metrics,users,consultations,payments',
            'filters' => 'nullable|array',
            'description' => 'nullable|string',
        ]);

        // TODO: Implement report creation
        $report = [
            'id' => now()->timestamp,
            'name' => $validated['name'],
            'type' => $validated['type'],
            'created_at' => now(),
        ];

        return $this->success($report, 'Report created successfully', 201);
    }

    /**
     * Update report
     * PUT /api/v1/analytics/reports/{id}
     */
    public function updateReport(Request $request, $id): JsonResponse
    {
        if (Auth::user()->role !== 'admin') {
            return $this->error('Unauthorized', [], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'filters' => 'nullable|array',
        ]);

        // TODO: Implement report update
        $report = [
            'id' => $id,
            'name' => $validated['name'],
            'updated_at' => now(),
        ];

        return $this->success($report, 'Report updated successfully');
    }

    /**
     * Delete report
     * DELETE /api/v1/analytics/reports/{id}
     */
    public function deleteReport($id): JsonResponse
    {
        if (Auth::user()->role !== 'admin') {
            return $this->error('Unauthorized', [], 403);
        }

        // TODO: Implement report deletion
        
        return $this->success([], 'Report deleted successfully');
    }

    /**
     * Clear analytics cache
     * POST /api/v1/analytics/clear-cache
     * 
     * Admin only - triggers cache refresh
     */
    public function clearCache(): JsonResponse
    {
        if (Auth::user()->role !== 'admin') {
            return $this->error('Unauthorized', [], 403);
        }

        $this->analyticsService->clearCache();

        return $this->success([], 'Analytics cache cleared successfully');
    }
}
