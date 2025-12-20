<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Events\NotificationCreated;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

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

        // Broadcast notification via WebSocket
        try {
            broadcast(new NotificationCreated($notification));
        } catch (\Exception $e) {
            Log::warning('Failed to broadcast notification: ' . $e->getMessage());
        }

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
     * Get unread notifications with eager loading
     */
    public function getUnreadNotifications($userId, $limit = 10)
    {
        return Notification::forUser($userId)
            ->unread()
            ->with(['user', 'related_model'])  // Eager load relationships
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

    /**
     * Notify when appointment is created
     */
    public function notifyAppointmentCreated($doctorId, $appointmentId, $patientName, $scheduledAt)
    {
        return $this->create(
            $doctorId,
            'appointment_created',
            "{$patientName} telah membuat janji temu pada {$scheduledAt}",
            "/appointments/{$appointmentId}"
        );
    }

    /**
     * Notify when appointment is confirmed
     */
    public function notifyAppointmentConfirmed($patientId, $appointmentId, $doctorName)
    {
        return $this->create(
            $patientId,
            'appointment_confirmed',
            "Dr. {$doctorName} telah mengkonfirmasi janji temu Anda",
            "/appointments/{$appointmentId}"
        );
    }

    /**
     * Notify when appointment is rejected
     */
    public function notifyAppointmentRejected($patientId, $appointmentId, $reason)
    {
        return $this->create(
            $patientId,
            'appointment_rejected',
            "Janji temu Anda ditolak. Alasan: {$reason}",
            "/appointments/{$appointmentId}"
        );
    }

    /**
     * Notify when appointment is cancelled
     */
    public function notifyAppointmentCancelled($recipientId, $appointmentId, $reason = null)
    {
        $message = "Janji temu telah dibatalkan";
        if ($reason) {
            $message .= ". Alasan: {$reason}";
        }
        
        return $this->create(
            $recipientId,
            'appointment_cancelled',
            $message,
            "/appointments/{$appointmentId}"
        );
    }

    /**
     * Notify when appointment is rescheduled
     */
    public function notifyAppointmentRescheduled($recipientId, $appointmentId, $newDate)
    {
        return $this->create(
            $recipientId,
            'appointment_rescheduled',
            "Janji temu Anda telah dijadwalkan ulang menjadi {$newDate}",
            "/appointments/{$appointmentId}"
        );
    }

    /**
     * Notify when appointment starts
     */
    public function notifyAppointmentStarted($recipientId, $appointmentId, $participantName)
    {
        return $this->create(
            $recipientId,
            'appointment_started',
            "Janji temu dengan {$participantName} sedang dimulai",
            "/appointments/{$appointmentId}"
        );
    }

    /**
     * Notify when appointment is completed
     */
    public function notifyAppointmentCompleted($recipientId, $appointmentId)
    {
        return $this->create(
            $recipientId,
            'appointment_completed',
            "Janji temu Anda telah selesai",
            "/appointments/{$appointmentId}"
        );
    }

    /**
     * ============================================
     * REAL-TIME NOTIFICATION METHODS
     * ============================================
     */

    /**
     * Broadcast real-time notification untuk konsultasi baru
     */
    public function broadcastNewConsultation($konsultasi)
    {
        $dokter = $konsultasi->dokter->user;
        
        $notification = [
            'type' => 'new_consultation',
            'konsultasi_id' => $konsultasi->id,
            'pasien_name' => $konsultasi->pasien->user->nama,
            'pasien_id' => $konsultasi->pasien_id,
            'message' => "Konsultasi baru dari " . $konsultasi->pasien->user->nama,
            'created_at' => now()->toIso8601String(),
        ];
        
        $this->create(
            $dokter->id,
            self::TYPE_CONSULTATION,
            $notification['message'],
            "/consultations/{$konsultasi->id}"
        );
        
        Log::info('New consultation broadcast sent', ['konsultasi_id' => $konsultasi->id]);
    }

    /**
     * Broadcast real-time notification untuk pesan baru
     */
    public function broadcastNewMessage($message, $konsultasi)
    {
        $sender = $message->pengirim;
        $senderType = $sender->role === 'dokter' ? 'dokter' : 'pasien';
        
        // Tentukan penerima
        if ($senderType === 'dokter') {
            $recipient = $konsultasi->pasien->user;
        } else {
            $recipient = $konsultasi->dokter->user;
        }
        
        $notification = [
            'type' => 'new_message',
            'konsultasi_id' => $konsultasi->id,
            'message_id' => $message->id,
            'sender_name' => $sender->nama,
            'sender_type' => $senderType,
            'message_preview' => substr($message->pesan, 0, 100),
            'created_at' => now()->toIso8601String(),
        ];
        
        $this->create(
            $recipient->id,
            self::TYPE_MESSAGE,
            "Pesan baru dari {$sender->nama}",
            "/consultations/{$konsultasi->id}/messages"
        );
        
        Log::info('New message broadcast sent', ['message_id' => $message->id]);
    }

    /**
     * Broadcast perubahan status konsultasi
     */
    public function broadcastConsultationStatusChange($konsultasi, $oldStatus, $newStatus)
    {
        $statusMessages = [
            'pending' => 'Menunggu',
            'active' => 'Berlangsung',
            'closed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
        ];
        
        $message = "Status konsultasi berubah dari " . 
                   ($statusMessages[$oldStatus] ?? $oldStatus) . " menjadi " . 
                   ($statusMessages[$newStatus] ?? $newStatus);
        
        // Notify both parties
        $this->create(
            $konsultasi->pasien->user_id,
            self::TYPE_CONSULTATION,
            $message,
            "/consultations/{$konsultasi->id}"
        );
        
        $this->create(
            $konsultasi->dokter->user_id,
            self::TYPE_CONSULTATION,
            $message,
            "/consultations/{$konsultasi->id}"
        );
        
        Log::info('Consultation status change broadcast sent', [
            'konsultasi_id' => $konsultasi->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
        ]);
    }

    /**
     * Broadcast konsultasi diterima
     */
    public function broadcastConsultationAccepted($konsultasi)
    {
        $this->create(
            $konsultasi->pasien->user_id,
            self::TYPE_CONSULTATION,
            "Konsultasi Anda diterima oleh " . $konsultasi->dokter->user->nama,
            "/consultations/{$konsultasi->id}"
        );
        
        Log::info('Consultation accepted broadcast sent', ['konsultasi_id' => $konsultasi->id]);
    }

    /**
     * Broadcast konsultasi ditolak
     */
    public function broadcastConsultationRejected($konsultasi, $reason = '')
    {
        $message = "Konsultasi Anda ditolak oleh " . $konsultasi->dokter->user->nama;
        if ($reason) {
            $message .= ": " . $reason;
        }
        
        $this->create(
            $konsultasi->pasien->user_id,
            self::TYPE_CONSULTATION,
            $message,
            "/consultations/{$konsultasi->id}"
        );
        
        Log::info('Consultation rejected broadcast sent', ['konsultasi_id' => $konsultasi->id]);
    }

    /**
     * Broadcast konsultasi selesai
     */
    public function broadcastConsultationCompleted($konsultasi)
    {
        $this->create(
            $konsultasi->pasien->user_id,
            self::TYPE_CONSULTATION,
            "Konsultasi Anda telah selesai. Berikan rating untuk dokter.",
            "/consultations/{$konsultasi->id}/rating"
        );
        
        Log::info('Consultation completed broadcast sent', ['konsultasi_id' => $konsultasi->id]);
    }

    /**
     * Broadcast consultation status change via WebSocket
     */
    public function broadcastConsultationStatus($consultation, $status, $message = null)
    {
        try {
            $eventClass = 'App\\Events\\ConsultationStatusBroadcast';
            if (class_exists($eventClass)) {
                broadcast(new $eventClass($consultation, $status, $message));
                Log::info('Consultation status broadcast sent', [
                    'consultation_id' => $consultation->id,
                    'status' => $status,
                ]);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to broadcast consultation status: ' . $e->getMessage());
        }
    }

    /**
     * Broadcast appointment update via WebSocket
     */
    public function broadcastAppointmentUpdate($appointment, $action, $details = [])
    {
        try {
            $eventClass = 'App\\Events\\AppointmentUpdateBroadcast';
            if (class_exists($eventClass)) {
                broadcast(new $eventClass($appointment, $action, $details));
                Log::info('Appointment update broadcast sent', [
                    'appointment_id' => $appointment->id,
                    'action' => $action,
                ]);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to broadcast appointment update: ' . $e->getMessage());
        }
    }

    /**
     * Broadcast new message via WebSocket
     */
    public function broadcastMessage($conversationId, $type = 'sent', $data = [])
    {
        try {
            $eventClass = 'App\\Events\\MessageBroadcast';
            if (class_exists($eventClass)) {
                broadcast(new $eventClass($conversationId, $type, $data));
                Log::info('Message broadcast sent', [
                    'conversation_id' => $conversationId,
                    'type' => $type,
                ]);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to broadcast message: ' . $e->getMessage());
        }
    }

    /**
     * Broadcast notification via WebSocket
     */
    public function broadcastNotification($notification)
    {
        try {
            $eventClass = 'App\\Events\\NotificationBroadcast';
            if (class_exists($eventClass)) {
                broadcast(new $eventClass($notification));
                Log::info('Notification broadcast sent', [
                    'notification_id' => $notification->id,
                    'user_id' => $notification->user_id,
                ]);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to broadcast notification: ' . $e->getMessage());
        }
    }
}


