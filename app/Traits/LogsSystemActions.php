<?php

namespace App\Traits;

use App\Models\SystemLog;

trait LogsSystemActions
{
    /**
     * Log a system action
     */
    protected static function logAction(
        $adminId,
        $action,
        $resource,
        $resourceId = null,
        $changes = null,
        $status = 'success'
    ) {
        try {
            SystemLog::logAction(
                $adminId,
                $action,
                $resource,
                $resourceId,
                null,
                $changes,
                null,
                $status
            );
        } catch (\Exception $e) {
            // Silently fail - don't let logging errors break the application
            \Log::error('Failed to log system action', [
                'error' => $e->getMessage(),
                'action' => $action,
                'resource' => $resource
            ]);
        }
    }
}
