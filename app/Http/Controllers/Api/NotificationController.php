<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

/**
 * ============================================
 * NOTIFICATION CONTROLLER
 * ============================================
 * 
 * Menangani notifikasi untuk users
 * 
 * Endpoint:
 * GET /api/v1/notifications - Get user notifications (paginated)
 * GET /api/v1/notifications/unread - Get unread notifications
 * GET /api/v1/notifications/count - Get unread count
 * GET /api/v1/notifications/stats - Get notification statistics
 * POST /api/v1/notifications/{id}/read - Mark single as read
 * POST /api/v1/notifications/read-multiple - Mark multiple as read
 * POST /api/v1/notifications/read-all - Mark all as read
 * DELETE /api/v1/notifications/{id} - Delete notification
 * DELETE /api/v1/notifications/delete-multiple - Delete multiple
 * DELETE /api/v1/notifications/clear - Clear all notifications
 * 
 * @author Aplikasi Telemedicine RSUD dr. R. Soedarsono
 * @version 1.0
 */
class NotificationController extends Controller
{
    use ApiResponse;

    public function __construct(
        private NotificationService $notificationService
    ) {}

    /**
     * Get user notifications dengan pagination
     * 
     * Query params:
     * - page: integer (default 1)
     * - per_page: integer (default 20, max 100)
     * 
     * GET /api/v1/notifications
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $page = $request->query('page', 1);
        $perPage = min($request->query('per_page', 20), 100);

        $result = $this->notificationService->getUserNotifications($user->id, $page, $perPage);

        return $this->successResponse($result['data'], 'Notifications berhasil diambil', [
            'pagination' => $result['pagination'],
        ]);
    }

    /**
     * Get unread notifications
     * 
     * Query params:
     * - limit: integer (default 10, max 50)
     * 
     * GET /api/v1/notifications/unread
     */
    public function getUnread(Request $request)
    {
        $user = $request->user();
        $limit = min($request->query('limit', 10), 50);

        $notifications = $this->notificationService->getUnreadNotifications($user->id, $limit);

        return $this->successResponse($notifications, 'Unread notifications berhasil diambil');
    }

    /**
     * Get unread count
     * 
     * GET /api/v1/notifications/count
     */
    public function getUnreadCount(Request $request)
    {
        $user = $request->user();
        $count = $this->notificationService->getUnreadCount($user->id);

        return $this->successResponse([
            'unread_count' => $count,
        ], 'Unread count berhasil diambil');
    }

    /**
     * Get notification statistics
     * 
     * GET /api/v1/notifications/stats
     */
    public function getStats(Request $request)
    {
        $user = $request->user();
        $stats = $this->notificationService->getStats($user->id);

        return $this->successResponse($stats, 'Notification stats berhasil diambil');
    }

    /**
     * Mark single notification as read
     * 
     * POST /api/v1/notifications/{id}/read
     */
    public function markAsRead(Request $request, $notificationId)
    {
        $user = $request->user();

        try {
            $notification = $this->notificationService->markAsRead($notificationId, $user->id);
            return $this->successResponse($notification, 'Notification ditandai sebagai dibaca');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Notification tidak ditemukan');
        }
    }

    /**
     * Mark multiple notifications as read
     * 
     * Request body:
     * {
     *   "notification_ids": [1, 2, 3]
     * }
     * 
     * POST /api/v1/notifications/read-multiple
     */
    public function markMultipleAsRead(Request $request)
    {
        $request->validate([
            'notification_ids' => 'required|array|min:1',
            'notification_ids.*' => 'integer',
        ]);

        $user = $request->user();
        $result = $this->notificationService->markMultipleAsRead(
            $request->notification_ids,
            $user->id
        );

        return $this->successResponse($result, "Notifications ditandai sebagai dibaca ({$result['count']} items)");
    }

    /**
     * Mark all notifications as read
     * 
     * POST /api/v1/notifications/read-all
     */
    public function markAllAsRead(Request $request)
    {
        $user = $request->user();
        $result = $this->notificationService->markAllAsRead($user->id);

        return $this->successResponse($result, "Semua notifications ditandai sebagai dibaca ({$result['count']} items)");
    }

    /**
     * Delete single notification
     * 
     * DELETE /api/v1/notifications/{id}
     */
    public function destroy(Request $request, $notificationId)
    {
        $user = $request->user();

        try {
            $result = $this->notificationService->delete($notificationId, $user->id);
            return $this->successResponse($result, 'Notification berhasil dihapus');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Notification tidak ditemukan');
        }
    }

    /**
     * Delete multiple notifications
     * 
     * Request body:
     * {
     *   "notification_ids": [1, 2, 3]
     * }
     * 
     * DELETE /api/v1/notifications/delete-multiple
     */
    public function deleteMultiple(Request $request)
    {
        $request->validate([
            'notification_ids' => 'required|array|min:1',
            'notification_ids.*' => 'integer',
        ]);

        $user = $request->user();
        $result = $this->notificationService->deleteMultiple(
            $request->notification_ids,
            $user->id
        );

        return $this->successResponse($result, "Notifications berhasil dihapus ({$result['count']} items)");
    }

    /**
     * Clear all notifications
     * 
     * DELETE /api/v1/notifications/clear
     */
    public function clearAll(Request $request)
    {
        $user = $request->user();
        $result = $this->notificationService->clearAll($user->id);

        return $this->successResponse($result, "Semua notifications berhasil dihapus ({$result['count']} items)");
    }
}
