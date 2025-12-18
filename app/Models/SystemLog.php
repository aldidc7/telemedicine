<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SystemLog extends Model
{
    use HasFactory;

    protected $table = 'system_logs';
    protected $fillable = [
        'admin_id',
        'action',
        'resource',
        'resource_id',
        'ip_address',
        'user_agent',
        'changes',
        'status'
    ];

    protected $casts = [
        'changes' => 'json',
        'created_at' => 'datetime',
    ];

    /**
     * Relationship: SystemLog belongs to User (admin)
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Scope: Filter by admin
     */
    public function scopeByAdmin($query, $adminId)
    {
        return $query->where('admin_id', $adminId);
    }

    /**
     * Scope: Filter by action
     */
    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope: Filter by resource
     */
    public function scopeByResource($query, $resource)
    {
        return $query->where('resource', $resource);
    }

    /**
     * Scope: Filter by resource ID
     */
    public function scopeForResource($query, $resource, $resourceId)
    {
        return $query->where('resource', $resource)
                     ->where('resource_id', $resourceId);
    }

    /**
     * Scope: Recent logs first
     */
    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Scope: Filter by date range
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope: Filter by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Static: Log an action
     * 
     * @param int $adminId
     * @param string $action (create, read, update, delete, download, export)
     * @param string $resource (dokter, pasien, user, konsultasi, etc)
     * @param int $resourceId
     * @param string $ipAddress
     * @param array $changes
     * @param string $userAgent
     * @param string $status (success, failed)
     * @return SystemLog
     */
    public static function logAction(
        $adminId,
        $action,
        $resource,
        $resourceId = null,
        $ipAddress = null,
        $changes = null,
        $userAgent = null,
        $status = 'success'
    ) {
        return self::create([
            'admin_id' => $adminId,
            'action' => $action,
            'resource' => $resource,
            'resource_id' => $resourceId,
            'ip_address' => $ipAddress ?? request()->ip(),
            'user_agent' => $userAgent ?? request()->userAgent(),
            'changes' => $changes,
            'status' => $status,
        ]);
    }

    /**
     * Get action badge color
     */
    public function getActionBadgeColor()
    {
        return match($this->action) {
            'create' => 'bg-green-100 text-green-800',
            'update' => 'bg-blue-100 text-blue-800',
            'delete' => 'bg-red-100 text-red-800',
            'read', 'view' => 'bg-gray-100 text-gray-800',
            'download', 'export' => 'bg-purple-100 text-purple-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get resource badge color
     */
    public function getResourceBadgeColor()
    {
        return match($this->resource) {
            'dokter' => 'bg-indigo-100 text-indigo-800',
            'pasien' => 'bg-green-100 text-green-800',
            'user' => 'bg-orange-100 text-orange-800',
            'konsultasi' => 'bg-blue-100 text-blue-800',
            'config' => 'bg-purple-100 text-purple-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}
