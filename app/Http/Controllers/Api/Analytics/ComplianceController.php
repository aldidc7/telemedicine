<?php

namespace App\Http\Controllers\Api\Analytics;

use App\Http\Controllers\Api\ApiController;
use App\Services\Analytics\ComplianceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ComplianceController extends ApiController
{
    protected $complianceService;

    public function __construct(ComplianceService $complianceService)
    {
        $this->complianceService = $complianceService;
    }

    /**
     * Dapatkan dashboard compliance
     * GET /api/v1/compliance/dashboard
     */
    public function dashboard()
    {
        if (Auth::user()->role !== 'admin') {
            return $this->error('Unauthorized', 403);
        }

        try {
            $dashboard = $this->complianceService->getDashboard();
            return $this->success($dashboard, 'Dashboard compliance berhasil diambil');
        } catch (\Exception $e) {
            return $this->error('Gagal mengambil dashboard: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Dapatkan log audit dengan filtering
     * GET /api/v1/compliance/audit-logs?action=&role=&days=30&page=1
     */
    public function auditLogs(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            return $this->error('Unauthorized', 403);
        }

        try {
            $action = $request->query('action');
            $userRole = $request->query('role');
            $days = $request->query('days', 30);
            $page = $request->query('page', 1);

            $logs = $this->complianceService->getAuditLogs(
                $action,
                $userRole,
                (int)$days,
                (int)$page
            );

            return $this->success($logs, 'Audit logs berhasil diambil');
        } catch (\Exception $e) {
            return $this->error('Gagal mengambil audit logs: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Dapatkan status credential verification
     * GET /api/v1/compliance/credentials
     */
    public function credentials()
    {
        if (Auth::user()->role !== 'admin') {
            return $this->error('Unauthorized', 403);
        }

        try {
            $status = $this->complianceService->getCredentialVerificationStatus();
            return $this->success($status, 'Status kredensial dokter berhasil diambil');
        } catch (\Exception $e) {
            return $this->error('Gagal mengambil status kredensial: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Dapatkan tracking consent
     * GET /api/v1/compliance/consents
     */
    public function consents()
    {
        if (Auth::user()->role !== 'admin') {
            return $this->error('Unauthorized', 403);
        }

        try {
            $consents = $this->complianceService->getConsentTrackingStatus();
            return $this->success($consents, 'Status consent berhasil diambil');
        } catch (\Exception $e) {
            return $this->error('Gagal mengambil status consent: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Dapatkan data retention status
     * GET /api/v1/compliance/data-retention
     */
    public function dataRetention()
    {
        if (Auth::user()->role !== 'admin') {
            return $this->error('Unauthorized', 403);
        }

        try {
            $status = $this->complianceService->getDataRetentionStatus();
            return $this->success($status, 'Status data retention berhasil diambil');
        } catch (\Exception $e) {
            return $this->error('Gagal mengambil data retention: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Export compliance report untuk auditor
     * POST /api/v1/compliance/export
     */
    public function export(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            return $this->error('Unauthorized', 403);
        }

        try {
            $format = $request->input('format', 'json'); // json atau csv

            $report = [
                'exported_at' => now()->toDateTimeString(),
                'exporter_id' => Auth::user()->id,
                'exporter_name' => Auth::user()->name,
                'compliance_dashboard' => $this->complianceService->getDashboard(),
                'audit_logs' => $this->complianceService->getAuditLogs(null, null, 90),
                'credentials' => $this->complianceService->getCredentialVerificationStatus(),
                'consents' => $this->complianceService->getConsentTrackingStatus(),
                'data_retention' => $this->complianceService->getDataRetentionStatus(),
                'compliance_score' => $this->complianceService->calculateComplianceScore(),
            ];

            if ($format === 'csv') {
                return $this->exportAsCSV($report);
            }

            return $this->success($report, 'Compliance report berhasil diekspor');
        } catch (\Exception $e) {
            return $this->error('Gagal mengekspor report: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Export sebagai CSV
     */
    private function exportAsCSV(array $report): \Illuminate\Http\Response
    {
        $filename = 'compliance-report-' . now()->format('Y-m-d-H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $output = fopen('php://output', 'w');
        
        // Header
        fputcsv($output, ['Compliance Report']);
        fputcsv($output, ['Exported', $report['exported_at']]);
        fputcsv($output, ['Exporter', $report['exporter_name']]);
        fputcsv($output, []);

        // Overall Compliance
        fputcsv($output, ['Overall Compliance Status']);
        $overall = $report['compliance_dashboard']['overall_status'];
        fputcsv($output, ['Status', $overall['overall']]);
        fputcsv($output, ['Passed', $overall['passed']]);
        fputcsv($output, ['Total Checks', $overall['total']]);
        fputcsv($output, []);

        // Compliance Score
        fputcsv($output, ['Compliance Score']);
        fputcsv($output, ['Score', $report['compliance_score'] . '%']);
        fputcsv($output, []);

        // Credentials
        fputcsv($output, ['Doctor Credentials Status']);
        fputcsv($output, ['Total Doctors', $report['credentials']['total_doctors']]);
        fputcsv($output, ['Verified', $report['credentials']['verified']]);
        fputcsv($output, ['Expired', $report['credentials']['expired']]);
        fputcsv($output, ['Percentage', $report['credentials']['percentage_verified'] . '%']);
        fputcsv($output, []);

        // Consents
        fputcsv($output, ['Consent Tracking']);
        foreach ($report['consents']['consent_types'] as $type => $data) {
            fputcsv($output, [$type, $data['accepted'], $data['rejected'], $data['percentage_accepted'] . '%']);
        }

        fclose($output);

        return response('', 200, $headers);
    }

    /**
     * Clear cache
     * POST /api/v1/compliance/clear-cache
     */
    public function clearCache()
    {
        if (Auth::user()->role !== 'admin') {
            return $this->error('Unauthorized', 403);
        }

        try {
            $this->complianceService->clearCache();
            return $this->success([], 'Cache berhasil dihapus');
        } catch (\Exception $e) {
            return $this->error('Gagal menghapus cache: ' . $e->getMessage(), 500);
        }
    }
}
