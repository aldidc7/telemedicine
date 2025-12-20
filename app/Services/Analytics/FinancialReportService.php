<?php

namespace App\Services\Analytics;

use App\Models\Payment;
use App\Models\Invoice;
use App\Models\Konsultasi;
use App\Models\Dokter;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class FinancialReportService
{
    const CACHE_DURATION = 180; // 3 hours for financial reports

    /**
     * Get financial dashboard overview
     */
    public function getDashboard(): array
    {
        return Cache::remember('finance.dashboard', self::CACHE_DURATION * 60, function () {
            return [
                'total_revenue' => $this->getTotalRevenue(),
                'monthly_revenue' => $this->getMonthlyRevenue(),
                'pending_revenue' => $this->getPendingRevenue(),
                'total_payments' => $this->getTotalPayments(),
                'completed_payments' => $this->getCompletedPayments(),
                'failed_payments' => $this->getFailedPayments(),
                'refunded_amount' => $this->getRefundedAmount(),
                'average_transaction' => $this->getAverageTransaction(),
                'growth_rate' => $this->getRevenueGrowthRate(),
                'timestamp' => now(),
            ];
        });
    }

    /**
     * Get revenue analytics
     */
    public function getRevenueAnalytics(string $period = 'monthly'): array
    {
        $cacheKey = "finance.revenue.$period";
        
        return Cache::remember($cacheKey, self::CACHE_DURATION * 60, function () use ($period) {
            return [
                'period' => $period,
                'total_revenue' => $this->getTotalRevenue(),
                'by_month' => $this->getRevenueByMonth(),
                'by_year' => $this->getRevenueByYear(),
                'by_doctor' => $this->getRevenueByDoctor(),
                'by_payment_method' => $this->getRevenueByPaymentMethod(),
                'growth_trend' => $this->getGrowthTrend($period),
                'forecast' => $this->getForecast(),
            ];
        });
    }

    /**
     * Get invoice tracking
     */
    public function getInvoiceTracking(?string $status = null): array
    {
        $query = Invoice::query();
        
        if ($status) {
            $query->where('status', $status);
        }
        
        $invoices = $query->with('doctor:id,nama_dokter')
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'doctor_id' => $invoice->doctor_id,
                    'doctor_name' => $invoice->doctor->nama_dokter ?? 'N/A',
                    'amount' => round($invoice->amount, 2),
                    'status' => $invoice->status,
                    'issued_at' => $invoice->issued_at?->toDateString(),
                    'due_at' => $invoice->due_at?->toDateString(),
                    'paid_at' => $invoice->paid_at?->toDateString(),
                    'days_overdue' => $this->calculateDaysOverdue($invoice),
                ];
            });
        
        return [
            'invoices' => $invoices->toArray(),
            'total' => $invoices->count(),
            'total_amount' => $invoices->sum('amount'),
            'status_breakdown' => $this->getInvoiceStatusBreakdown(),
        ];
    }

    /**
     * Get payment analytics
     */
    public function getPaymentAnalytics(): array
    {
        return Cache::remember('finance.payments', self::CACHE_DURATION * 60, function () {
            return [
                'total_transactions' => Payment::count(),
                'total_amount' => (float) Payment::sum('amount'),
                'by_status' => $this->getPaymentsByStatus(),
                'by_method' => $this->getPaymentsByMethod(),
                'success_rate' => $this->getPaymentSuccessRate(),
                'average_amount' => (float) Payment::avg('amount'),
                'monthly_trend' => $this->getPaymentMonthlyTrend(),
                'daily_trend' => $this->getPaymentDailyTrend(),
            ];
        });
    }

    /**
     * Get refund analytics
     */
    public function getRefundAnalytics(): array
    {
        return Cache::remember('finance.refunds', self::CACHE_DURATION * 60, function () {
            return [
                'total_refunds' => Payment::where('status', 'refunded')->count(),
                'total_refunded_amount' => (float) Payment::where('status', 'refunded')->sum('amount'),
                'refund_rate' => $this->getRefundRate(),
                'by_reason' => $this->getRefundsByReason(),
                'average_refund' => (float) Payment::where('status', 'refunded')->avg('amount'),
                'pending_refunds' => Payment::where('status', 'refund_pending')->count(),
                'pending_refund_amount' => (float) Payment::where('status', 'refund_pending')->sum('amount'),
                'refund_trend' => $this->getRefundTrend(),
            ];
        });
    }

    /**
     * Get cash flow analysis
     */
    public function getCashFlow(): array
    {
        return [
            'period' => 'last_12_months',
            'inflows' => $this->getCashInflows(),
            'outflows' => $this->getCashOutflows(),
            'net_cash_flow' => $this->getNetCashFlow(),
            'cash_position' => $this->getCashPosition(),
            'liquidity_ratio' => $this->getLiquidityRatio(),
        ];
    }

    /**
     * Generate monthly financial report
     */
    public function generateMonthlyReport(?int $month = null, ?int $year = null): array
    {
        $month = $month ?? now()->month;
        $year = $year ?? now()->year;
        
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        
        return [
            'period' => $startDate->format('Y-m'),
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'summary' => [
                'total_revenue' => $this->getRevenueForPeriod($startDate, $endDate),
                'total_expenses' => $this->getExpensesForPeriod($startDate, $endDate),
                'net_profit' => $this->getNetProfitForPeriod($startDate, $endDate),
                'transactions' => Payment::whereBetween('created_at', [$startDate, $endDate])->count(),
            ],
            'by_doctor' => $this->getDoctorFinancialsForPeriod($startDate, $endDate),
            'by_payment_method' => $this->getPaymentMethodsForPeriod($startDate, $endDate),
            'invoices' => $this->getInvoicesForPeriod($startDate, $endDate),
            'refunds' => $this->getRefundsForPeriod($startDate, $endDate),
        ];
    }

    /**
     * Generate yearly financial report
     */
    public function generateYearlyReport(?int $year = null): array
    {
        $year = $year ?? now()->year;
        
        $startDate = Carbon::createFromDate($year, 1, 1)->startOfYear();
        $endDate = $startDate->copy()->endOfYear();
        
        $monthlyData = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthStart = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $monthEnd = $monthStart->copy()->endOfMonth();
            
            $monthlyData[$monthStart->format('Y-m')] = [
                'revenue' => $this->getRevenueForPeriod($monthStart, $monthEnd),
                'expenses' => $this->getExpensesForPeriod($monthStart, $monthEnd),
                'profit' => $this->getNetProfitForPeriod($monthStart, $monthEnd),
            ];
        }
        
        return [
            'year' => $year,
            'summary' => [
                'total_revenue' => $this->getRevenueForPeriod($startDate, $endDate),
                'total_expenses' => $this->getExpensesForPeriod($startDate, $endDate),
                'net_profit' => $this->getNetProfitForPeriod($startDate, $endDate),
                'transactions' => Payment::whereBetween('created_at', [$startDate, $endDate])->count(),
            ],
            'monthly_breakdown' => $monthlyData,
            'by_doctor' => $this->getDoctorFinancialsForPeriod($startDate, $endDate),
            'by_quarter' => $this->getQuarterlyBreakdown($year),
        ];
    }

    /**
     * Clear financial cache
     */
    public function clearCache(): void
    {
        Cache::forget('finance.dashboard');
        Cache::forget('finance.revenue.monthly');
        Cache::forget('finance.revenue.yearly');
        Cache::forget('finance.payments');
        Cache::forget('finance.refunds');
    }

    // ===== Private Helper Methods =====

    private function getTotalRevenue(): float
    {
        return (float) Payment::where('status', 'completed')->sum('amount');
    }

    private function getMonthlyRevenue(): float
    {
        return (float) Payment::where('status', 'completed')
            ->where('created_at', '>=', Carbon::now()->startOfMonth())
            ->sum('amount');
    }

    private function getPendingRevenue(): float
    {
        return (float) Payment::where('status', 'pending')->sum('amount');
    }

    private function getTotalPayments(): int
    {
        return Payment::count();
    }

    private function getCompletedPayments(): int
    {
        return Payment::where('status', 'completed')->count();
    }

    private function getFailedPayments(): int
    {
        return Payment::where('status', 'failed')->count();
    }

    private function getRefundedAmount(): float
    {
        return (float) Payment::where('status', 'refunded')->sum('amount');
    }

    private function getAverageTransaction(): float
    {
        return (float) (Payment::avg('amount') ?? 0);
    }

    private function getRevenueGrowthRate(): float
    {
        $thisMonth = $this->getMonthlyRevenue();
        $lastMonth = (float) Payment::where('status', 'completed')
            ->where('created_at', '>=', Carbon::now()->subMonth()->startOfMonth())
            ->where('created_at', '<', Carbon::now()->startOfMonth())
            ->sum('amount');
        
        if ($lastMonth === 0) {
            return 0;
        }
        
        return round((($thisMonth - $lastMonth) / $lastMonth) * 100, 2);
    }

    private function getRevenueByMonth(): array
    {
        return Payment::where('status', 'completed')
            ->selectRaw('DATE_TRUNC(\'month\', created_at) as month, SUM(amount) as total')
            ->where('created_at', '>=', Carbon::now()->subYear())
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('total', 'month')
            ->toArray();
    }

    private function getRevenueByYear(): array
    {
        return Payment::where('status', 'completed')
            ->selectRaw('EXTRACT(YEAR FROM created_at) as year, SUM(amount) as total')
            ->groupBy('year')
            ->orderBy('year')
            ->get()
            ->pluck('total', 'year')
            ->toArray();
    }

    private function getRevenueByDoctor(): array
    {
        return Payment::selectRaw('d.id, d.nama_dokter, SUM(p.amount) as total')
            ->join('konsultasi as k', 'p.consultation_id', '=', 'k.id')
            ->join('dokter as d', 'k.doctor_id', '=', 'd.id')
            ->where('p.status', 'completed')
            ->groupBy('d.id', 'd.nama_dokter')
            ->orderByDesc('total')
            ->get()
            ->toArray();
    }

    private function getRevenueByPaymentMethod(): array
    {
        return Payment::selectRaw('metode_pembayaran, COUNT(*) as transactions, SUM(amount) as total')
            ->where('status', 'completed')
            ->groupBy('metode_pembayaran')
            ->get()
            ->map(function ($item) {
                return [
                    'method' => $item->metode_pembayaran,
                    'transactions' => $item->transactions,
                    'total' => round($item->total, 2),
                ];
            })
            ->toArray();
    }

    private function getGrowthTrend(string $period): array
    {
        $trend = [];
        
        if ($period === 'monthly') {
            for ($i = 11; $i >= 0; $i--) {
                $month = Carbon::now()->subMonths($i);
                $revenue = (float) Payment::where('status', 'completed')
                    ->whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->sum('amount');
                
                $trend[$month->format('Y-m')] = $revenue;
            }
        }
        
        return $trend;
    }

    private function getForecast(): array
    {
        $avgMonthly = (float) Payment::where('status', 'completed')
            ->where('created_at', '>=', Carbon::now()->subMonths(3))
            ->selectRaw('AVG(MONTH(created_at)) * SUM(amount)')
            ->value('SUM(amount)') / 3;
        
        return [
            'next_month_forecast' => round($avgMonthly, 2),
            'next_quarter_forecast' => round($avgMonthly * 3, 2),
            'confidence' => '70%',
        ];
    }

    private function calculateDaysOverdue($invoice): int
    {
        if (!$invoice->due_at || $invoice->status === 'paid') {
            return 0;
        }
        
        return max(0, Carbon::parse($invoice->due_at)->diffInDays(now(), false));
    }

    private function getInvoiceStatusBreakdown(): array
    {
        return Invoice::selectRaw('status, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();
    }

    private function getPaymentsByStatus(): array
    {
        return Payment::selectRaw('status, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('status')
            ->get()
            ->map(function ($item) {
                return [
                    'status' => $item->status,
                    'count' => $item->count,
                    'total' => round($item->total, 2),
                ];
            })
            ->toArray();
    }

    private function getPaymentsByMethod(): array
    {
        return Payment::selectRaw('metode_pembayaran, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('metode_pembayaran')
            ->get()
            ->map(function ($item) {
                return [
                    'method' => $item->metode_pembayaran,
                    'count' => $item->count,
                    'total' => round($item->total, 2),
                ];
            })
            ->toArray();
    }

    private function getPaymentSuccessRate(): float
    {
        $total = Payment::count();
        if ($total === 0) {
            return 0;
        }
        
        $successful = Payment::where('status', 'completed')->count();
        return round(($successful / $total) * 100, 2);
    }

    private function getPaymentMonthlyTrend(): array
    {
        return Payment::selectRaw('DATE_TRUNC(\'month\', created_at) as month, COUNT(*) as count')
            ->where('created_at', '>=', Carbon::now()->subYear())
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('count', 'month')
            ->toArray();
    }

    private function getPaymentDailyTrend(): array
    {
        return Payment::selectRaw('DATE(created_at) as day, COUNT(*) as count')
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->pluck('count', 'day')
            ->toArray();
    }

    private function getRefundRate(): float
    {
        $total = Payment::count();
        if ($total === 0) {
            return 0;
        }
        
        $refunded = Payment::where('status', 'refunded')->count();
        return round(($refunded / $total) * 100, 2);
    }

    private function getRefundsByReason(): array
    {
        return Payment::where('status', 'refunded')
            ->selectRaw('refund_reason, COUNT(*) as count')
            ->groupBy('refund_reason')
            ->get()
            ->pluck('count', 'refund_reason')
            ->toArray();
    }

    private function getRefundTrend(): array
    {
        return Payment::where('status', 'refunded')
            ->selectRaw('DATE_TRUNC(\'month\', updated_at) as month, SUM(amount) as total')
            ->where('updated_at', '>=', Carbon::now()->subYear())
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('total', 'month')
            ->toArray();
    }

    private function getCashInflows(): array
    {
        return [
            'completed_payments' => (float) Payment::where('status', 'completed')->sum('amount'),
            'pending_payments' => (float) Payment::where('status', 'pending')->sum('amount'),
        ];
    }

    private function getCashOutflows(): array
    {
        $platformExpenses = (float) Payment::where('status', 'completed')->sum('amount') * 0.30;
        
        return [
            'platform_fees' => $platformExpenses,
            'refunds' => $this->getRefundedAmount(),
        ];
    }

    private function getNetCashFlow(): float
    {
        $inflows = array_sum($this->getCashInflows());
        $outflows = array_sum($this->getCashOutflows());
        
        return round($inflows - $outflows, 2);
    }

    private function getCashPosition(): float
    {
        return (float) Payment::where('status', 'completed')->sum('amount') 
            - Payment::where('status', 'refunded')->sum('amount');
    }

    private function getLiquidityRatio(): float
    {
        $assets = $this->getCashPosition();
        $liabilities = (float) Payment::where('status', 'refund_pending')->sum('amount');
        
        if ($liabilities === 0) {
            return 100;
        }
        
        return round(($assets / $liabilities), 2);
    }

    private function getRevenueForPeriod(Carbon $startDate, Carbon $endDate): float
    {
        return (float) Payment::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('amount');
    }

    private function getExpensesForPeriod(Carbon $startDate, Carbon $endDate): float
    {
        $revenue = $this->getRevenueForPeriod($startDate, $endDate);
        return round($revenue * 0.30, 2); // Platform takes 30%
    }

    private function getNetProfitForPeriod(Carbon $startDate, Carbon $endDate): float
    {
        $revenue = $this->getRevenueForPeriod($startDate, $endDate);
        $expenses = $this->getExpensesForPeriod($startDate, $endDate);
        
        return round($revenue - $expenses, 2);
    }

    private function getDoctorFinancialsForPeriod(Carbon $startDate, Carbon $endDate): array
    {
        return Payment::selectRaw('d.id, d.nama_dokter, SUM(p.amount) as revenue')
            ->join('konsultasi as k', 'p.consultation_id', '=', 'k.id')
            ->join('dokter as d', 'k.doctor_id', '=', 'd.id')
            ->where('p.status', 'completed')
            ->whereBetween('p.created_at', [$startDate, $endDate])
            ->groupBy('d.id', 'd.nama_dokter')
            ->orderByDesc('revenue')
            ->get()
            ->toArray();
    }

    private function getPaymentMethodsForPeriod(Carbon $startDate, Carbon $endDate): array
    {
        return Payment::selectRaw('metode_pembayaran, COUNT(*) as count, SUM(amount) as total')
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('metode_pembayaran')
            ->get()
            ->map(function ($item) {
                return [
                    'method' => $item->metode_pembayaran,
                    'transactions' => $item->count,
                    'total' => round($item->total, 2),
                ];
            })
            ->toArray();
    }

    private function getInvoicesForPeriod(Carbon $startDate, Carbon $endDate): array
    {
        return Invoice::whereBetween('created_at', [$startDate, $endDate])
            ->get()
            ->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'number' => $invoice->invoice_number,
                    'amount' => $invoice->amount,
                    'status' => $invoice->status,
                ];
            })
            ->toArray();
    }

    private function getRefundsForPeriod(Carbon $startDate, Carbon $endDate): array
    {
        return Payment::where('status', 'refunded')
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->selectRaw('COUNT(*) as count, SUM(amount) as total')
            ->get()
            ->map(function ($item) {
                return [
                    'count' => $item->count,
                    'total' => round($item->total, 2),
                ];
            })
            ->toArray();
    }

    private function getQuarterlyBreakdown(int $year): array
    {
        $quarters = [];
        
        for ($q = 1; $q <= 4; $q++) {
            $startMonth = ($q - 1) * 3 + 1;
            $endMonth = $startMonth + 2;
            
            $startDate = Carbon::createFromDate($year, $startMonth, 1)->startOfMonth();
            $endDate = Carbon::createFromDate($year, $endMonth, 1)->endOfMonth();
            
            $quarters["Q$q"] = [
                'revenue' => $this->getRevenueForPeriod($startDate, $endDate),
                'expenses' => $this->getExpensesForPeriod($startDate, $endDate),
                'profit' => $this->getNetProfitForPeriod($startDate, $endDate),
            ];
        }
        
        return $quarters;
    }
}
