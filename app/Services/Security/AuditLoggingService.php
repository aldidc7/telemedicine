<?php

namespace App\Services\Security;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

/**
 * ============================================
 * SERVICE: AUDIT LOGGING
 * ============================================
 * 
 * Track semua aktivitas sensitif untuk compliance
 * - Data access logs
 * - Modification logs
 * - Authentication events
 * - Export/deletion logs
 * 
 * Required for: GDPR, HIPAA, compliance audits
 */
class AuditLoggingService
{
    const ACTION_LOGIN = 'LOGIN';
    const ACTION_LOGOUT = 'LOGOUT';
    const ACTION_CREATE = 'CREATE';
    const ACTION_UPDATE = 'UPDATE';
    const ACTION_DELETE = 'DELETE';
    const ACTION_VIEW = 'VIEW';
    const ACTION_EXPORT = 'EXPORT';
    const ACTION_IMPORT = 'IMPORT';
    const ACTION_DOWNLOAD = 'DOWNLOAD';
    const ACTION_SHARE = 'SHARE';
    const ACTION_CONSENT_ACCEPT = 'CONSENT_ACCEPT';
    const ACTION_CONSENT_REVOKE = 'CONSENT_REVOKE';
    const ACTION_UPLOAD = 'UPLOAD';

    /**
     * Log user action
     * 
     * @param string $action - Type of action (LOGIN, CREATE, UPDATE, etc)
     * @param string $resource - What resource (User, Consultation, etc)
     * @param int|null $resourceId - ID of resource
     * @param array $changes - What changed (old → new)
     * @param string|null $reason - Why (deletion reason, etc)
     * @return void
     */
    public static function log(
        string $action,
        string $resource,
        ?int $resourceId = null,
        array $changes = [],
        ?string $reason = null
    ): void {
        $userId = Auth::id();

        $logData = [
            'timestamp' => Carbon::now()->toIso8601String(),
            'action' => $action,
            'resource' => $resource,
            'resource_id' => $resourceId,
            'user_id' => $userId,
            'user_name' => Auth::user()?->name ?? 'Anonymous',
            'ip_address' => request()?->ip() ?? 'Unknown',
            'user_agent' => request()?->userAgent() ?? 'Unknown',
        ];

        // Add changes if sensitive action
        if (in_array($action, [self::ACTION_CREATE, self::ACTION_UPDATE, self::ACTION_DELETE])) {
            $logData['changes'] = self::maskSensitiveData($changes);
        }

        // Add reason if provided (for deletions, etc)
        if ($reason) {
            $logData['reason'] = $reason;
        }

        // Determine log channel based on action
        $channel = self::getLogChannel($action);

        Log::channel($channel)->info("Audit Log: {$action}", $logData);
    }

    /**
     * Log authentication event
     */
    public static function logAuth(string $action, ?int $userId = null, bool $success = true): void
    {
        self::log(
            $action,
            'Authentication',
            $userId,
            ['success' => $success]
        );
    }

    /**
     * Log consultation access
     */
    public static function logConsultationAccess(int $consultationId, int $userId, string $action = self::ACTION_VIEW): void
    {
        self::log(
            $action,
            'Consultation',
            $consultationId,
            ['accessed_by' => $userId]
        );
    }

    /**
     * Log patient data access (HIPAA required)
     */
    public static function logPatientDataAccess(int $patientId, string $dataType, int $accessedBy): void
    {
        self::log(
            self::ACTION_VIEW,
            "PatientData-{$dataType}",
            $patientId,
            ['data_type' => $dataType, 'accessed_by' => $accessedBy]
        );
    }

    /**
     * Log prescription action
     */
    public static function logPrescription(string $action, int $prescriptionId, array $details = []): void
    {
        self::log(
            $action,
            'Prescription',
            $prescriptionId,
            $details
        );
    }

    /**
     * Log data export (GDPR portability)
     */
    public static function logDataExport(int $userId, string $dataType, int $recordCount, string $format): void
    {
        self::log(
            self::ACTION_EXPORT,
            "DataExport-{$dataType}",
            $userId,
            [
                'format' => $format,
                'record_count' => $recordCount,
                'exported_at' => now(),
            ],
            "User requested data export for {$dataType}"
        );
    }

    /**
     * Log data deletion (GDPR right to be forgotten)
     */
    public static function logDataDeletion(int $userId, string $dataType, int $recordCount, string $reason): void
    {
        self::log(
            self::ACTION_DELETE,
            "DataDeletion-{$dataType}",
            $userId,
            ['record_count' => $recordCount],
            $reason
        );
    }

    /**
     * Log consent action
     */
    public static function logConsent(string $action, int $userId, string $consentType, bool $granted): void
    {
        self::log(
            $action,
            "Consent-{$consentType}",
            $userId,
            ['granted' => $granted]
        );
    }

    /**
     * Log file upload
     */
    public static function logFileUpload(int $userId, string $fileName, int $fileSize, string $filePath): void
    {
        self::log(
            self::ACTION_UPLOAD,
            'FileUpload',
            null,
            [
                'file_name' => $fileName,
                'file_size' => $fileSize,
                'file_path' => $filePath,
            ]
        );
    }

    /**
     * Log message send (chat)
     */
    public static function logMessage(int $messageId, int $senderId, int $consultationId, int $fileSize = 0): void
    {
        self::log(
            'MESSAGE_SENT',
            'Message',
            $messageId,
            [
                'sender_id' => $senderId,
                'consultation_id' => $consultationId,
                'has_attachment' => $fileSize > 0,
                'file_size' => $fileSize,
            ]
        );
    }

    /**
     * Mask sensitive data sebelum logging
     */
    private static function maskSensitiveData(array $data): array
    {
        $sensitive = ['password', 'token', 'secret', 'ssn', 'phone', 'email'];

        foreach ($data as $key => $value) {
            if (is_string($key)) {
                $lowerKey = strtolower($key);
                foreach ($sensitive as $sensitive_field) {
                    if (strpos($lowerKey, $sensitive_field) !== false) {
                        $data[$key] = '***MASKED***';
                        break;
                    }
                }
            }
        }

        return $data;
    }

    /**
     * Determine log channel based on action priority
     */
    private static function getLogChannel(string $action): string
    {
        $criticalActions = [
            self::ACTION_DELETE,
            self::ACTION_EXPORT,
            self::ACTION_CONSENT_REVOKE,
        ];

        if (in_array($action, $criticalActions)) {
            return 'audit_critical';
        }

        return 'audit';
    }

    /**
     * Get audit logs untuk user (untuk compliance requests)
     */
    public static function getUserAuditLog(int $userId, $limit = 100)
    {
        return Log::channel('audit')->info("Audit Log Query for User {$userId}", [
            'requested_at' => now(),
            'limit' => $limit,
        ]);
    }

    /**
     * Get sensitive data access logs
     */
    public static function getSensitiveAccessLogs(string $resourceType, ?int $resourceId = null, $days = 90)
    {
        // This would query actual audit log database if implemented
        // For now, returns placeholder for testing
        return Log::channel('audit')->info("Sensitive Data Access Report", [
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'days' => $days,
            'generated_at' => now(),
        ]);
    }
}
