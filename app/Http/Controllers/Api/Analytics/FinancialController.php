<?php

namespace App\Http\Controllers\Api\Analytics;

use App\Http\Controllers\Api\ApiController;
use App\Services\Analytics\FinancialReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FinancialController extends ApiController
{
    protected $reportService;

    public function __construct(FinancialReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Get financial dashboard
     * GET /api/v1/finance/dashboard
     */
    public function dashboard(): JsonResponse
    {
        if (Auth::user()->role !== 'admin') {
            return $this->error('Unauthorized', [], 403);
        }

        $dashboard = $this->reportService->getDashboard();

        return $this->success($dashboard, 'Financial dashboard retrieved successfully');
    }

    /**
     * Get revenue analytics
     * GET /api/v1/finance/revenue
     */
    public function revenue(Request $request): JsonResponse
    {
        if (Auth::user()->role !== 'admin') {
            return $this->error('Unauthorized', [], 403);
        }

        $period = $request->query('period', 'monthly');
        $analytics = $this->reportService->getRevenueAnalytics($period);

        return $this->success($analytics, 'Revenue analytics retrieved successfully');
    }

    /**
     * Get invoice tracking
     * GET /api/v1/finance/invoices
     */
    public function invoices(Request $request): JsonResponse
    {
        if (Auth::user()->role !== 'admin') {
            return $this->error('Unauthorized', [], 403);
        }

        $status = $request->query('status');
        $invoices = $this->reportService->getInvoiceTracking($status);

        return $this->success($invoices, 'Invoices retrieved successfully');
    }

    /**
     * Get payment analytics
     * GET /api/v1/finance/payments
     */
    public function payments(): JsonResponse
    {
        if (Auth::user()->role !== 'admin') {
            return $this->error('Unauthorized', [], 403);
        }

        $analytics = $this->reportService->getPaymentAnalytics();

        return $this->success($analytics, 'Payment analytics retrieved successfully');
    }

    /**
     * Get refund analytics
     * GET /api/v1/finance/refunds
     */
    public function refunds(): JsonResponse
    {
        if (Auth::user()->role !== 'admin') {
            return $this->error('Unauthorized', [], 403);
        }

        $analytics = $this->reportService->getRefundAnalytics();

        return $this->success($analytics, 'Refund analytics retrieved successfully');
    }

    /**
     * Get cash flow analysis
     * GET /api/v1/finance/cash-flow
     */
    public function cashFlow(): JsonResponse
    {
        if (Auth::user()->role !== 'admin') {
            return $this->error('Unauthorized', [], 403);
        }

        $cashFlow = $this->reportService->getCashFlow();

        return $this->success($cashFlow, 'Cash flow analysis retrieved successfully');
    }

    /**
     * Generate monthly financial report
     * POST /api/v1/finance/reports/monthly
     */
    public function monthlyReport(Request $request): JsonResponse
    {
        if (Auth::user()->role !== 'admin') {
            return $this->error('Unauthorized', [], 403);
        }

        $validated = $request->validate([
            'month' => 'nullable|integer|between:1,12',
            'year' => 'nullable|integer|min:2020',
        ]);

        $month = $validated['month'] ?? null;
        $year = $validated['year'] ?? null;

        $report = $this->reportService->generateMonthlyReport($month, $year);

        return $this->success($report, 'Monthly financial report generated successfully');
    }

    /**
     * Generate yearly financial report
     * POST /api/v1/finance/reports/yearly
     */
    public function yearlyReport(Request $request): JsonResponse
    {
        if (Auth::user()->role !== 'admin') {
            return $this->error('Unauthorized', [], 403);
        }

        $validated = $request->validate([
            'year' => 'nullable|integer|min:2020',
        ]);

        $year = $validated['year'] ?? null;

        $report = $this->reportService->generateYearlyReport($year);

        return $this->success($report, 'Yearly financial report generated successfully');
    }

    /**
     * Clear financial cache
     * POST /api/v1/finance/clear-cache
     */
    public function clearCache(): JsonResponse
    {
        if (Auth::user()->role !== 'admin') {
            return $this->error('Unauthorized', [], 403);
        }

        $this->reportService->clearCache();

        return $this->success([], 'Financial cache cleared successfully');
    }
}
