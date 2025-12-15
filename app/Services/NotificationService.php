<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class NotificationService
{
    /**
     * Notification Types
     */
    const TYPE_MESSAGE = 'new_message';              // Pesan masuk
    const TYPE_APPOINTMENT = 'appointment_update';   // Update appointment
    const TYPE_DOCTOR_APPROVED = 'doctor_approved';  // Dokter disetujui
    const TYPE_DOCTOR_REJECTED = 'doctor_rejected';  // Dokter ditolak
    const TYPE_CONSULTATION = 'consultation_update'; // Update konsultasi
    const TYPE_RATING = 'rating_received';           // Rating diterima
    const TYPE_VERIFICATION = 'email_verified';      // Email verified
    const TYPE_SYSTEM = 'system_message';            // Pesan sistem

    /**
     * Notification Titles
     */
    private static $titles = [
        self::TYPE_MESSAGE => 'Pesan Baru',
        self::TYPE_APPOINTMENT => 'Update Appointment',
        self::TYPE_DOCTOR_APPROVED => 'Dokter Disetujui',
        self::TYPE_DOCTOR_REJECTED => 'Dokter Ditolak',
        self::TYPE_CONSULTATION => 'Update Konsultasi',
        self::TYPE_RATING => 'Rating Diterima',
        self::TYPE_VERIFICATION => 'Email Terverifikasi',
        self::TYPE_SYSTEM => 'Notifikasi Sistem',
    ];

    /**
     * Create notification
     */
    public function create(
        $userId,
        $type,
        $message,
        $actionUrl = null,
        Model $notifiable = null
    ) {
        $title = self::$titles[$type] ?? 'Notifikasi';

        $notification = Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'action_url' => $actionUrl,
            'notifiable_type' => $notifiable ? get_class($notifiable) : null,
            'notifiable_id' => $notifiable ? $notifiable->id : null,
        ]);

        return $notification;
    }

    /**
     * Send notification to multiple users
     */
    public function sendToMany(
        array $userIds,
        $type,
        $message,
        $actionUrl = null,
        Model $notifiable = null
    ) {
        $notifications = [];

        foreach ($userIds as $userId) {
            $notifications[] = $this->create(
                $userId,
                $type,
                $message,
                $actionUrl,
                $notifiable
            );
        }

        return $notifications;
    }

    /**
     * Get user's notifications dengan pagination
     */
    public function getUserNotifications($userId, $page = 1, $perPage = 20)
    {
        $notifications = Notification::forUser($userId)
            ->recent()
            ->paginate($perPage, ['*'], 'page', $page);

        return [
            'data' => $notifications->items(),
            'pagination' => [
                'total' => $notifications->total(),
                'per_page' => $notifications->perPage(),
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
            ],
        ];
    }

    /**
     * Get unread notifications
     */
    public function getUnreadNotifications($userId, $limit = 10)
    {
        return Notification::forUser($userId)
            ->unread()
            ->recent()
            ->limit($limit)
            ->get();
    }

    /**
     * Get unread count
     */
    public function getUnreadCount($userId)
    {
        return Notification::forUser($userId)
            ->unread()
            ->count();
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($notificationId, $userId)
    {
        $notification = Notification::where('id', $notificationId)
            ->where('user_id', $userId)
            ->firstOrFail();

        $notification->markAsRead();

        return $notification;
    }

    /**
     * Mark multiple notifications as read
     */
    public function markMultipleAsRead(array $notificationIds, $userId)
    {
        Notification::whereIn('id', $notificationIds)
            ->where('user_id', $userId)
            ->update(['read_at' => now()]);

        return [
            'success' => true,
            'count' => count($notificationIds),
        ];
    }

    /**
     * Mark all notifications as read for user
     */
    public function markAllAsRead($userId)
    {
        $count = Notification::forUser($userId)
            ->unread()
            ->update(['read_at' => now()]);

        return [
            'success' => true,
            'count' => $count,
        ];
    }

    /**
     * Delete notification
     */
    public function delete($notificationId, $userId)
    {
        $notification = Notification::where('id', $notificationId)
            ->where('user_id', $userId)
            ->firstOrFail();

        $notification->delete();

        return ['success' => true];
    }

    /**
     * Delete multiple notifications
     */
    public function deleteMultiple(array $notificationIds, $userId)
    {
        Notification::whereIn('id', $notificationIds)
            ->where('user_id', $userId)
            ->delete();

        return [
            'success' => true,
            'count' => count($notificationIds),
        ];
    }

    /**
     * Clear all notifications for user
     */
    public function clearAll($userId)
    {
        $count = Notification::forUser($userId)
            ->delete();

        return [
            'success' => true,
            'count' => $count,
        ];
    }

    /**
     * Get notification statistics
     */
    public function getStats($userId)
    {
        $total = Notification::forUser($userId)->count();
        $unread = Notification::forUser($userId)->unread()->count();
        $byType = Notification::forUser($userId)
            ->groupBy('type')
            ->selectRaw('type, count(*) as count')
            ->pluck('count', 'type')
            ->toArray();

        return [
            'total' => $total,
            'unread' => $unread,
            'by_type' => $byType,
        ];
    }

    /**
     * Create message notification (when someone sends a message)
     */
    public function notifyNewMessage($recipientUserId, $senderName, $messagePreview, $conversationId)
    {
        return $this->create(
            $recipientUserId,
            self::TYPE_MESSAGE,
            "Pesan dari {$senderName}: {$messagePreview}",
            "/messages/conversations/{$conversationId}"
        );
    }

    /**
     * Create appointment notification
     */
    public function notifyAppointmentUpdate($userId, $doctorName, $date, $status)
    {
        $message = "Appointment dengan {$doctorName} pada {$date}: {$status}";

        return $this->create(
            $userId,
            self::TYPE_APPOINTMENT,
            $message,
            "/appointments"
        );
    }

    /**
     * Create doctor approval notification
     */
    public function notifyDoctorApproved($doctorUserId, $adminName)
    {
        return $this->create(
            $doctorUserId,
            self::TYPE_DOCTOR_APPROVED,
            "Profil dokter Anda telah disetujui oleh {$adminName}",
            "/profile"
        );
    }

    /**
     * Create doctor rejection notification
     */
    public function notifyDoctorRejected($doctorUserId, $reason)
    {
        return $this->create(
            $doctorUserId,
            self::TYPE_DOCTOR_REJECTED,
            "Profil dokter Anda ditolak: {$reason}",
            "/profile"
        );
    }

    /**
     * Create consultation update notification
     */
    public function notifyConsultationUpdate($userId, $status, $consultationId)
    {
        return $this->create(
            $userId,
            self::TYPE_CONSULTATION,
            "Status konsultasi Anda berubah menjadi: {$status}",
            "/consultations/{$consultationId}"
        );
    }

    /**
     * Create rating notification
     */
    public function notifyRatingReceived($doctorUserId, $rating, $patientName)
    {
        return $this->create(
            $doctorUserId,
            self::TYPE_RATING,
            "{$patientName} memberi rating {$rating} bintang",
            "/ratings"
        );
    }

    /**
     * Create verification notification
     */
    public function notifyEmailVerified($userId)
    {
        return $this->create(
            $userId,
            self::TYPE_VERIFICATION,
            'Email Anda telah berhasil diverifikasi',
            "/profile"
        );
    }

    /**
     * Create system notification
     */
    public function notifySystem($userId, $message)
    {
        return $this->create(
            $userId,
            self::TYPE_SYSTEM,
            $message
        );
    }

    /**
     * Notify prescription created
     */
    public function notifyPrescriptionCreated($userId, $prescriptionId, $doctorName, $medicationCount)
    {
        return $this->create(
            $userId,
            'prescription_created',
            "Dr. {$doctorName} telah memberikan resep dengan {$medicationCount} obat",
            "/prescriptions/{$prescriptionId}"
        );
    }

    /**
     * Notify prescription updated
     */
    public function notifyPrescriptionUpdated($userId, $prescriptionId, $doctorName)
    {
        return $this->create(
            $userId,
            'prescription_updated',
            "Dr. {$doctorName} telah mengupdate resep Anda",
            "/prescriptions/{$prescriptionId}"
        );
    }

    /**
     * Notify prescription acknowledged
     */
    public function notifyPrescriptionAcknowledged($userId, $prescriptionId, $patientName)
    {
        return $this->create(
            $userId,
            'prescription_acknowledged',
            "{$patientName} telah mengconfirm resep Anda",
            "/prescriptions/{$prescriptionId}"
        );
    }
}
