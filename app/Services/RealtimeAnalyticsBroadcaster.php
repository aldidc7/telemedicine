<?php

namespace App\Services;

use App\Models\Konsultasi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Real-time Analytics Broadcaster Service
 * Handles WebSocket events and real-time metric updates
 */
class RealtimeAnalyticsBroadcaster
{
    private AnalyticsService $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Broadcast consultation metric updates to admins
     */
    public function broadcastConsultationMetrics($period = 'today')
    {
        try {
            $metrics = $this->analyticsService->getConsultationMetrics($period);
            $userId = auth('sanctum')->check() ? auth('sanctum')->id() : null;
            
            // Broadcast to all admin users
            broadcast(new \App\Events\AnalyticsMetricsUpdated(
                'CONSULTATION_UPDATE',
                $metrics,
                $userId
            ))->toOthers();

            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to broadcast consultation metrics:', [
                'error' => $e->getMessage(),
                'period' => $period
            ]);
            return false;
        }
    }

    /**
     * Broadcast doctor performance updates
     */
    public function broadcastDoctorPerformance($limit = 10)
    {
        try {
            $performance = $this->analyticsService->getDoctorPerformance($limit);
            $userId = auth('sanctum')->check() ? auth('sanctum')->id() : null;
            
            broadcast(new \App\Events\AnalyticsMetricsUpdated(
                'DOCTOR_PERFORMANCE_UPDATE',
                $performance,
                $userId
            ))->toOthers();

            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to broadcast doctor performance:', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Broadcast revenue updates
     */
    public function broadcastRevenueMetrics($period = 'month')
    {
        try {
            $revenue = $this->analyticsService->getRevenueAnalytics($period);
            $userId = auth('sanctum')->check() ? auth('sanctum')->id() : null;
            
            broadcast(new \App\Events\AnalyticsMetricsUpdated(
                'REVENUE_UPDATE',
                $revenue,
                $userId
            ))->toOthers();

            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to broadcast revenue metrics:', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Broadcast health trends updates
     */
    public function broadcastHealthTrends()
    {
        try {
            $trends = $this->analyticsService->getPatientHealthTrends();
            $userId = auth('sanctum')->check() ? auth('sanctum')->id() : null;
            
            broadcast(new \App\Events\AnalyticsMetricsUpdated(
                'HEALTH_TRENDS_UPDATE',
                $trends,
                $userId
            ))->toOthers();

            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to broadcast health trends:', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Broadcast all analytics updates
     */
    public function broadcastAllMetrics()
    {
        $results = [
            'consultations' => $this->broadcastConsultationMetrics(),
            'doctor_performance' => $this->broadcastDoctorPerformance(),
            'revenue' => $this->broadcastRevenueMetrics(),
            'health_trends' => $this->broadcastHealthTrends(),
        ];

        return array_all_succeeded($results);
    }

    /**
     * Handle incoming WebSocket client request
     */
    public function handleClientRequest($userId, $type, $payload = [])
    {
        switch ($type) {
            case 'REQUEST_METRICS':
                return $this->handleMetricsRequest($userId, $payload['metrics'] ?? []);
            
            case 'REQUEST_CONSULTATION_METRICS':
                return $this->broadcastConsultationMetrics($payload['period'] ?? 'today');
            
            case 'REQUEST_DOCTOR_PERFORMANCE':
                return $this->broadcastDoctorPerformance($payload['limit'] ?? 10);
            
            case 'REQUEST_REVENUE':
                return $this->broadcastRevenueMetrics($payload['period'] ?? 'month');
            
            case 'REQUEST_HEALTH_TRENDS':
                return $this->broadcastHealthTrends();
            
            default:
                \Log::warning('Unknown WebSocket request type:', ['type' => $type]);
                return false;
        }
    }

    /**
     * Handle multiple metrics request
     */
    private function handleMetricsRequest($userId, $metrics)
    {
        $results = [];

        foreach ($metrics as $metric) {
            switch ($metric) {
                case 'consultations':
                    $results['consultations'] = $this->broadcastConsultationMetrics();
                    break;
                case 'doctors':
                    $results['doctors'] = $this->broadcastDoctorPerformance();
                    break;
                case 'revenue':
                    $results['revenue'] = $this->broadcastRevenueMetrics();
                    break;
                case 'health_trends':
                    $results['health_trends'] = $this->broadcastHealthTrends();
                    break;
            }
        }

        return $results;
    }

    /**
     * Trigger real-time update when consultation is created
     */
    public static function onConsultationCreated($consultation)
    {
        $service = new self(new AnalyticsService());
        $service->broadcastConsultationMetrics('today');
    }

    /**
     * Trigger real-time update when consultation is completed
     */
    public static function onConsultationCompleted($consultation)
    {
        $service = new self(new AnalyticsService());
        $service->broadcastConsultationMetrics('today');
        $service->broadcastRevenueMetrics('today');
    }

    /**
     * Trigger real-time update when rating is created
     */
    public static function onRatingCreated($rating)
    {
        $service = new self(new AnalyticsService());
        $service->broadcastDoctorPerformance();
    }
}
