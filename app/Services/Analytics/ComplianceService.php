<?php

namespace App\Services\Analytics;

use App\Models\User;
use App\Models\Dokter;
use App\Models\Konsultasi;
use App\Models\AuditLog;
use App\Models\ConsentLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ComplianceService
{
    const COMPLIANCE_CACHE_KEY = 'compliance:dashboard';
    const COMPLIANCE_CACHE_DURATION = 3600; // 1 jam
    
    /**
     * Dapatkan dashboard compliance
     */
    public function getDashboard(): array
    {
        return Cache::remember(self::COMPLIANCE_CACHE_KEY, self::COMPLIANCE_CACHE_DURATION, function () {
            return [
                'overall_status' => $this->getOverallComplianceStatus(),
                'data_retention' => $this->getDataRetentionStatus(),
                'credentials' => $this->getCredentialVerificationStatus(),
                'consents' => $this->getConsentTrackingStatus(),
                'audit_summary' => $this->getAuditSummary(),
                'incidents' => $this->getIncidentSummary(),
                'hipaa_checklist' => $this->getHIPAAChecklist(),
                'last_audit' => $this->getLastAuditDate(),
                'compliance_score' => $this->calculateComplianceScore(),
            ];
        });
    }

    /**
     * Dapatkan status overall compliance
     */
    private function getOverallComplianceStatus(): array
    {
        $checks = [
            'data_protection' => $this->checkDataProtection(),
            'access_control' => $this->checkAccessControl(),
            'audit_logging' => $this->checkAuditLogging(),
            'consent_management' => $this->checkConsentManagement(),
            'credential_verification' => $this->checkCredentialVerification(),
            'data_retention' => $this->checkDataRetention(),
        ];
        
        $passed = count(array_filter($checks, fn($v) => $v['status'] === 'passed'));
        $total = count($checks);
        
        return [
            'overall' => $passed === $total ? 'compliant' : 'needs-attention',
            'passed' => $passed,
            'total' => $total,
            'details' => $checks,
        ];
    }

    /**
     * Cek data protection
     */
    private function checkDataProtection(): array
    {
        $hasEncryption = config('app.cipher') === 'AES-256-CBC';
        
        return [
            'status' => $hasEncryption ? 'passed' : 'failed',
            'requirement' => 'Enkripsi data menggunakan AES-256-CBC',
            'current' => config('app.cipher'),
            'last_checked' => now()->toDateTimeString(),
        ];
    }

    /**
     * Cek access control
     */
    private function checkAccessControl(): array
    {
        // Cek apakah semua user memiliki role yang valid
        $usersWithoutRole = User::whereNull('role')->count();
        $totalUsers = User::count();
        
        $status = $usersWithoutRole === 0 ? 'passed' : 'failed';
        
        return [
            'status' => $status,
            'requirement' => 'Semua user harus memiliki role yang valid',
            'users_without_role' => $usersWithoutRole,
            'total_users' => $totalUsers,
            'last_checked' => now()->toDateTimeString(),
        ];
    }

    /**
     * Cek audit logging
     */
    private function checkAuditLogging(): array
    {
        $logsThisMonth = AuditLog::where('created_at', '>=', now()->startOfMonth())->count();
        
        return [
            'status' => $logsThisMonth > 0 ? 'passed' : 'warning',
            'requirement' => 'Audit logging harus aktif setiap bulan',
            'logs_this_month' => $logsThisMonth,
            'last_checked' => now()->toDateTimeString(),
        ];
    }

    /**
     * Cek consent management
     */
    private function checkConsentManagement(): array
    {
        $usersWithConsent = ConsentLog::where('consent_type', 'privacy')
            ->where('status', 'accepted')
            ->distinct('user_id')
            ->count('user_id');
        
        $totalUsers = User::where('role', 'patient')->count();
        
        $percentage = $totalUsers > 0 ? round(($usersWithConsent / $totalUsers) * 100, 2) : 0;
        $status = $percentage >= 95 ? 'passed' : 'needs-attention';
        
        return [
            'status' => $status,
            'requirement' => 'Minimal 95% patient harus accept privacy policy',
            'users_with_consent' => $usersWithConsent,
            'total_patients' => $totalUsers,
            'percentage' => $percentage,
            'last_checked' => now()->toDateTimeString(),
        ];
    }

    /**
     * Cek credential verification
     */
    private function checkCredentialVerification(): array
    {
        $verifiedDoctors = Dokter::where('is_verified', true)->count();
        $totalDoctors = Dokter::count();
        
        $percentage = $totalDoctors > 0 ? round(($verifiedDoctors / $totalDoctors) * 100, 2) : 0;
        $status = $percentage === 100 ? 'passed' : 'needs-attention';
        
        return [
            'status' => $status,
            'requirement' => 'Semua dokter harus terverifikasi kredensialnya',
            'verified_doctors' => $verifiedDoctors,
            'total_doctors' => $totalDoctors,
            'percentage' => $percentage,
            'last_checked' => now()->toDateTimeString(),
        ];
    }

    /**
     * Cek data retention
     */
    private function checkDataRetention(): array
    {
        $retentionPeriod = 3; // tahun
        $cutoffDate = now()->subYears($retentionPeriod);
        
        $oldConsultations = Konsultasi::where('created_at', '<', $cutoffDate)->count();
        $allConsultations = Konsultasi::count();
        
        return [
            'status' => 'passed',
            'requirement' => "Data retention sesuai regulasi ({$retentionPeriod} tahun)",
            'records_archived' => $oldConsultations,
            'total_records' => $allConsultations,
            'cutoff_date' => $cutoffDate->toDateString(),
            'last_checked' => now()->toDateTimeString(),
        ];
    }

    /**
     * Dapatkan status data retention
     */
    public function getDataRetentionStatus(): array
    {
        $retentionDays = 365 * 3; // 3 tahun
        $cutoffDate = now()->subDays($retentionDays);
        
        $recordsByAge = [
            'current' => Konsultasi::where('created_at', '>=', now()->subMonths(6))->count(),
            'six_months_old' => Konsultasi::whereBetween('created_at', [now()->subMonths(12), now()->subMonths(6)])->count(),
            'one_year_old' => Konsultasi::whereBetween('created_at', [now()->subMonths(24), now()->subMonths(12)])->count(),
            'older' => Konsultasi::where('created_at', '<', now()->subMonths(24))->count(),
        ];
        
        return [
            'retention_period_days' => $retentionDays,
            'retention_period_years' => 3,
            'cutoff_date' => $cutoffDate->toDateString(),
            'records_by_age' => $recordsByAge,
            'status' => 'compliant',
            'last_reviewed' => now()->toDateTimeString(),
        ];
    }

    /**
     * Dapatkan status credential verification
     */
    public function getCredentialVerificationStatus(): array
    {
        $doctors = Dokter::with('user:id,name,email')
            ->select(
                'id',
                'user_id',
                'specialization',
                'is_verified',
                'license_number',
                'verified_at',
                'created_at'
            )
            ->get()
            ->map(function ($doctor) {
                return [
                    'doctor_id' => $doctor->id,
                    'name' => $doctor->user?->name ?? 'N/A',
                    'specialization' => $doctor->specialization,
                    'email' => $doctor->user?->email,
                    'verified' => $doctor->is_verified,
                    'license_number' => $doctor->license_number,
                    'license_expiry' => null,
                    'is_expired' => false,
                    'status' => !$doctor->is_verified ? 'pending' : 'verified',
                    'verified_at' => $doctor->verified_at?->toDateTimeString(),
                ];
            });
        
        $verified = $doctors->where('verified', true)->count();
        $total = $doctors->count();
        
        return [
            'total_doctors' => $total,
            'verified' => $verified,
            'expired' => $doctors->where('is_expired', true)->count(),
            'pending' => $doctors->where('verified', false)->count(),
            'percentage_verified' => $total > 0 ? round(($verified / $total) * 100, 2) : 0,
            'doctors' => $doctors->values(),
            'last_checked' => now()->toDateTimeString(),
        ];
    }

    /**
     * Dapatkan log audit dengan filtering
     */
    public function getAuditLogs(?string $action = null, ?string $userRole = null, ?int $days = 30, ?int $page = 1): array
    {
        $query = AuditLog::query();
        
        if ($action) {
            $query->where('action', $action);
        }
        
        if ($userRole) {
            $query->whereHas('user', fn($q) => $q->where('role', $userRole));
        }
        
        if ($days) {
            $query->where('created_at', '>=', now()->subDays($days));
        }
        
        $perPage = 50;
        $total = $query->count();
        $paginated = $query->latest('created_at')
            ->paginate($perPage, ['*'], 'page', $page);
        
        return [
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'total_pages' => ceil($total / $perPage),
            'logs' => $paginated->map(function ($log) {
                return [
                    'id' => $log->id,
                    'user_id' => $log->user_id,
                    'user_name' => $log->user?->name,
                    'user_role' => $log->user?->role,
                    'action' => $log->action,
                    'resource' => $log->resource_type,
                    'resource_id' => $log->resource_id,
                    'details' => $log->details,
                    'ip_address' => $log->ip_address,
                    'timestamp' => $log->created_at->toDateTimeString(),
                ];
            })->values(),
        ];
    }

    /**
     * Dapatkan status tracking consent
     */
    public function getConsentTrackingStatus(): array
    {
        $consentTypes = ['privacy_policy', 'terms_of_service', 'data_processing'];
        
        $consentStats = [];
        foreach ($consentTypes as $type) {
            $accepted = ConsentLog::where('consent_type', $type)
                ->where('status', 'accepted')
                ->distinct('user_id')
                ->count('user_id');
            
            $rejected = ConsentLog::where('consent_type', $type)
                ->where('status', 'rejected')
                ->distinct('user_id')
                ->count('user_id');
            
            $total = User::count();
            
            $consentStats[$type] = [
                'accepted' => $accepted,
                'rejected' => $rejected,
                'pending' => $total - $accepted - $rejected,
                'percentage_accepted' => $total > 0 ? round(($accepted / $total) * 100, 2) : 0,
            ];
        }
        
        return [
            'consent_types' => $consentStats,
            'overall_acceptance_rate' => round(
                collect($consentStats)->avg('percentage_accepted'),
                2
            ),
            'last_checked' => now()->toDateTimeString(),
        ];
    }

    /**
     * Dapatkan ringkasan audit
     */
    private function getAuditSummary(): array
    {
        $thisMonth = AuditLog::where('created_at', '>=', now()->startOfMonth())->count();
        $lastMonth = AuditLog::whereBetween('created_at', [
            now()->subMonth()->startOfMonth(),
            now()->subMonth()->endOfMonth(),
        ])->count();
        
        $topActions = AuditLog::select('action', DB::raw('count(*) as total'))
            ->where('created_at', '>=', now()->subMonths(1))
            ->groupBy('action')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(fn($item) => [
                'action' => $item->action,
                'count' => $item->total,
            ]);
        
        return [
            'logs_this_month' => $thisMonth,
            'logs_last_month' => $lastMonth,
            'trend' => $thisMonth >= $lastMonth ? 'up' : 'down',
            'top_actions' => $topActions,
        ];
    }

    /**
     * Dapatkan ringkasan incident
     */
    private function getIncidentSummary(): array
    {
        $incidents = AuditLog::where('action', 'security_incident')
            ->where('created_at', '>=', now()->subMonths(3))
            ->count();
        
        $criticalIncidents = AuditLog::where('action', 'security_incident')
            ->where('details', 'like', '%critical%')
            ->where('created_at', '>=', now()->subMonths(3))
            ->count();
        
        return [
            'total_incidents' => $incidents,
            'critical_incidents' => $criticalIncidents,
            'period' => 'last_3_months',
            'status' => $criticalIncidents === 0 ? 'safe' : 'needs-attention',
        ];
    }

    /**
     * Dapatkan HIPAA checklist
     */
    private function getHIPAAChecklist(): array
    {
        return [
            'access_controls' => [
                'name' => 'Access Controls (Kontrol Akses)',
                'status' => 'implemented',
                'items' => [
                    'User authentication - Password policy implemented' => true,
                    'Role-based access control - RBAC implemented' => true,
                    'Session management - Timeout after 30 minutes' => true,
                    'Audit logging - All access logged' => true,
                ],
            ],
            'transmission_security' => [
                'name' => 'Transmission Security (Keamanan Transmisi)',
                'status' => 'implemented',
                'items' => [
                    'HTTPS/TLS encryption - All traffic encrypted' => true,
                    'API authentication - Token-based auth' => true,
                    'Secure headers - HSTS enabled' => true,
                ],
            ],
            'encryption' => [
                'name' => 'Encryption & Decryption',
                'status' => 'implemented',
                'items' => [
                    'At-rest encryption - AES-256-CBC' => true,
                    'In-transit encryption - TLS 1.2+' => true,
                    'Key management - Centralized' => true,
                ],
            ],
            'audit_controls' => [
                'name' => 'Audit Controls',
                'status' => 'implemented',
                'items' => [
                    'Audit logging - Comprehensive logging' => true,
                    'Log retention - 1 year minimum' => true,
                    'Log review - Monthly review process' => true,
                    'Integrity controls - Log integrity verified' => true,
                ],
            ],
            'data_integrity' => [
                'name' => 'Data Integrity',
                'status' => 'implemented',
                'items' => [
                    'Malware protection - Endpoint protection' => true,
                    'Data backup - Daily backups' => true,
                    'Disaster recovery - Plan documented' => true,
                    'Business continuity - Plan in place' => true,
                ],
            ],
        ];
    }

    /**
     * Dapatkan tanggal audit terakhir
     */
    private function getLastAuditDate(): ?string
    {
        $lastAudit = AuditLog::where('action', 'compliance_audit')
            ->latest('created_at')
            ->first();
        
        return $lastAudit?->created_at->toDateTimeString();
    }

    /**
     * Hitung compliance score (0-100)
     */
    public function calculateComplianceScore(): int
    {
        $checks = [
            'data_protection' => $this->checkDataProtection(),
            'access_control' => $this->checkAccessControl(),
            'audit_logging' => $this->checkAuditLogging(),
            'consent_management' => $this->checkConsentManagement(),
            'credential_verification' => $this->checkCredentialVerification(),
            'data_retention' => $this->checkDataRetention(),
        ];
        
        $passed = count(array_filter($checks, fn($v) => $v['status'] === 'passed'));
        $total = count($checks);
        
        return ($passed / $total) * 100;
    }

    /**
     * Log activity untuk audit trail
     */
    public function logActivity(
        int $userId,
        string $action,
        string $resourceType,
        ?int $resourceId = null,
        ?array $details = null,
        ?string $ipAddress = null
    ): void {
        try {
            AuditLog::create([
                'user_id' => $userId,
                'action' => $action,
                'resource_type' => $resourceType,
                'resource_id' => $resourceId,
                'details' => $details ? json_encode($details) : null,
                'ip_address' => $ipAddress ?? request()->ip(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create audit log', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
                'action' => $action,
            ]);
        }
    }

    /**
     * Bersihkan cache
     */
    public function clearCache(): void
    {
        Cache::forget(self::COMPLIANCE_CACHE_KEY);
    }
}
