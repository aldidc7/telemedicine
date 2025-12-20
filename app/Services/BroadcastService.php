<?php

namespace App\Services;

use App\Events\NotificationCreated;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Broadcast Service
 * 
 * Handle real-time notifications through WebSocket/Broadcasting channels
 */
class BroadcastService
{
    /**
     * Broadcast notification to user channel
     */
    public function broadcastNotification(Notification $notification): void
    {
        try {
            event(new NotificationCreated($notification));
            Log::info("Broadcasting notification {$notification->id} to user {$notification->user_id}");
        } catch (\Exception $e) {
            Log::error("Failed to broadcast notification: " . $e->getMessage());
        }
    }

    /**
     * Broadcast to multiple users
     */
    public function broadcastToUsers(array $userIds, Notification $notification): void
    {
        foreach ($userIds as $userId) {
            try {
                $user = User::find($userId);
                if ($user) {
                    $this->broadcastNotification($user, $notification);
                }
            } catch (\Exception $e) {
                Log::error("Failed to broadcast to user {$userId}: " . $e->getMessage());
            }
        }
    }

    /**
     * Broadcast appointment reminder
     */
    public function broadcastAppointmentReminder($appointment): void
    {
        $patient = $appointment->patient;
        $notification = Notification::create([
            'user_id' => $patient->id,
            'type' => Notification::TYPE_APPOINTMENT_REMINDER,
            'title' => 'Pengingat Janji Temu',
            'message' => "Janji temu Anda segera dimulai",
            'action_url' => "/appointments/{$appointment->id}",
        ]);

        $this->broadcastNotification($patient, $notification);
    }

    /**
     * Broadcast consultation started
     */
    public function broadcastConsultationStarted($consultation): void
    {
        $patient = $consultation->patient;
        $doctor = $consultation->doctor;

        $notification = Notification::create([
            'user_id' => $patient->id,
            'type' => Notification::TYPE_CONSULTATION_STARTED,
            'title' => 'Konsultasi Dimulai',
            'message' => "Konsultasi dengan Dr. {$doctor->nama} telah dimulai",
            'action_url' => "/consultations/{$consultation->id}",
        ]);

        $this->broadcastNotification($patient, $notification);
    }

    /**
     * Broadcast message notification
     */
    public function broadcastMessage($message, User $recipient): void
    {
        $sender = $message->sender;
        $notification = Notification::create([
            'user_id' => $recipient->id,
            'type' => Notification::TYPE_MESSAGE,
            'title' => 'Pesan Baru',
            'message' => "Pesan dari {$sender->nama}: " . substr($message->message, 0, 50),
            'action_url' => "/messages?conversation_with={$sender->id}",
        ]);

        $this->broadcastNotification($notification);
    }

    /**
     * Broadcast payment status
     */
    public function broadcastPaymentStatus($payment, string $status): void
    {
        $user = $payment->user;
        $type = $status === 'success' ? Notification::TYPE_PAYMENT_SUCCESS : Notification::TYPE_PAYMENT_FAILED;
        $title = $status === 'success' ? 'Pembayaran Berhasil' : 'Pembayaran Gagal';

        $notification = Notification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'message' => "Pembayaran Rp " . number_format($payment->amount, 0, ',', '.') . " telah " .
                         ($status === 'success' ? 'berhasil diproses' : 'gagal'),
            'action_url' => "/payments/{$payment->id}",
        ]);

        $this->broadcastNotification($user, $notification);
    }
}
