<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * ============================================
 * MODEL AUDIT LOG - COMPLIANCE TRACKING
 * ============================================
 * 
 * Track semua akses ke data pasien untuk compliance Indonesia
 * WAJIB ada untuk:
 * - Tracking siapa akses apa, kapan
 * - Security audit trail
 * - PII access monitoring
 * - Compliance dengan regulasi Indonesia
 * 
 * @property int $id
 * @property int $user_id - User yang akses
 * @property string $entity_type - Type of entity (patient, medical_record, etc)
 * @property int $entity_id - ID of entity yang diakses
 * @property string $action - Action (view, create, update, delete, download)
 * @property string $description - Deskripsi
 * @property array $changes - Changes (JSON)
 * @property string $ip_address - IP user
 * @property bool $accessed_pii - Access PII?
 * @property string $access_level - Level: public, protected, confidential
 * 
 * @author Aplikasi Telemedicine RSUD dr. R. Soedarsono
 * @version 1.0
 */
class AuditLog extends Model
{
    // No timestamps untuk created_at only
    public $timestamps = false;

    protected $table = 'audit_logs';

    protected $fillable = [
        'user_id',
        'entity_type',
        'entity_id',
        'action',
        'description',
        'changes',
        'ip_address',
        'user_agent',
        'accessed_pii',
        'access_level',
        'created_at',
        'resource_type',
        'resource_id',
        'details',
    ];

    protected $casts = [
        'changes' => 'array',
        'accessed_pii' => 'boolean',
        'created_at' => 'datetime',
    ];

    // ===== RELATIONSHIPS =====

    /**
     * Relation to User who accessed
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ===== SCOPES =====

    /**
     * Scope: Filter by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Filter by entity type
     */
    public function scopeForEntity($query, $entityType, $entityId = null)
    {
        $query->where('entity_type', $entityType);
        if ($entityId) {
            $query->where('entity_id', $entityId);
        }
        return $query;
    }

    /**
     * Scope: Filter by action
     */
    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope: Filter PII access only
     */
    public function scopeWithPiiAccess($query)
    {
        return $query->where('accessed_pii', true);
    }

    /**
     * Scope: Recent logs
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days))
                     ->orderBy('created_at', 'desc');
    }

    // ===== STATIC HELPERS =====

    /**
     * Log an access event
     */
    public static function logAccess($userId, $entityType, $entityId, $action, $options = [])
    {
        return static::create([
            'user_id' => $userId,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'action' => $action,
            'description' => $options['description'] ?? null,
            'changes' => $options['changes'] ?? null,
            'ip_address' => $options['ip_address'] ?? request()->ip(),
            'user_agent' => $options['user_agent'] ?? request()->userAgent(),
            'accessed_pii' => $options['accessed_pii'] ?? false,
            'access_level' => $options['access_level'] ?? 'protected',
            'created_at' => now(),
        ]);
    }

    /**
     * Log patient access
     */
    public static function logPatientAccess($userId, $patientId, $action, $description = null)
    {
        return static::logAccess($userId, 'patient', $patientId, $action, [
            'description' => $description,
            'accessed_pii' => true,  // Patient data adalah PII
            'access_level' => 'confidential',
        ]);
    }

    /**
     * Log medical record access
     */
    public static function logMedicalRecordAccess($userId, $recordId, $action, $description = null)
    {
        return static::logAccess($userId, 'medical_record', $recordId, $action, [
            'description' => $description,
            'accessed_pii' => true,  // Medical records adalah highly sensitive
            'access_level' => 'highly_confidential',
        ]);
    }
}
