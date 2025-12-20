<?php

namespace App\Services;

use App\Models\User;
use Pusher\Pusher;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class WebSocketService
{
    protected $pusher;

    public function __construct()
    {
        $this->pusher = new Pusher(
            env('PUSHER_APP_KEY'),
            env('PUSHER_APP_SECRET'),
            env('PUSHER_APP_ID'),
            [
                'host' => env('PUSHER_HOST', 'api-mt.pusher.com'),
                'port' => env('PUSHER_PORT', 443),
                'scheme' => env('PUSHER_SCHEME', 'https'),
                'encrypted' => true,
            ]
        );
    }

    /**
     * Broadcast new message to conversation channel
     */
    public function broadcastNewMessage(int $conversationId, array $message)
    {
        $channelName = "conversation.{$conversationId}";
        
        $this->pusher->trigger($channelName, 'message-sent', [
            'id' => $message['id'],
            'sender_id' => $message['sender_id'],
            'sender_name' => $message['sender_name'] ?? 'Unknown',
            'content' => $message['content'],
            'created_at' => $message['created_at'],
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Broadcast message read status
     */
    public function broadcastMessageRead(int $conversationId, int $userId)
    {
        $channelName = "conversation.{$conversationId}";
        
        $this->pusher->trigger($channelName, 'message-read', [
            'user_id' => $userId,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Broadcast new notification to user
     */
    public function broadcastNotification(int $userId, array $notification)
    {
        $channelName = "user.{$userId}.notifications";
        
        $this->pusher->trigger($channelName, 'notification-received', [
            'id' => $notification['id'],
            'type' => $notification['type'],
            'message' => $notification['message'],
            'action_url' => $notification['action_url'] ?? null,
            'created_at' => $notification['created_at'],
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Broadcast appointment status update
     */
    public function broadcastAppointmentUpdate(int $appointmentId, array $appointment)
    {
        // Broadcast to both patient and doctor
        $patientChannel = "user.{$appointment['patient_id']}.appointments";
        $doctorChannel = "user.{$appointment['doctor_id']}.appointments";
        
        $data = [
            'id' => $appointmentId,
            'status' => $appointment['status'],
            'scheduled_at' => $appointment['scheduled_at'],
            'doctor_name' => $appointment['doctor_name'] ?? 'Unknown',
            'patient_name' => $appointment['patient_name'] ?? 'Unknown',
            'timestamp' => now()->toIso8601String(),
        ];
        
        $this->pusher->trigger([$patientChannel, $doctorChannel], 'appointment-updated', $data);
    }

    /**
     * Broadcast prescription issued
     */
    public function broadcastPrescriptionCreated(int $patientId, array $prescription)
    {
        $channelName = "user.{$patientId}.prescriptions";
        
        $this->pusher->trigger($channelName, 'prescription-created', [
            'id' => $prescription['id'],
            'doctor_name' => $prescription['doctor_name'] ?? 'Unknown',
            'medication_count' => $prescription['medication_count'],
            'created_at' => $prescription['created_at'],
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Broadcast user online status
     */
    public function broadcastUserOnline(int $userId, string $status = 'online')
    {
        // Broadcast to all who follow this user
        $channelName = "user.{$userId}.status";
        
        $this->pusher->trigger($channelName, 'status-changed', [
            'user_id' => $userId,
            'status' => $status, // online, offline, away
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Broadcast doctor availability change
     */
    public function broadcastDoctorAvailabilityChange(int $doctorId, bool $available)
    {
        $channelName = "doctor.{$doctorId}.availability";
        
        $this->pusher->trigger($channelName, 'availability-changed', [
            'doctor_id' => $doctorId,
            'available' => $available,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Broadcast rating received
     */
    public function broadcastRatingReceived(int $doctorId, array $rating)
    {
        $channelName = "doctor.{$doctorId}.ratings";
        
        $this->pusher->trigger($channelName, 'rating-received', [
            'id' => $rating['id'],
            'rating' => $rating['rating'],
            'comment' => $rating['comment'] ?? '',
            'patient_name' => $rating['patient_name'] ?? 'Anonymous',
            'created_at' => $rating['created_at'],
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Broadcast typing indicator
     */
    public function broadcastTyping(int $conversationId, int $userId, string $userName)
    {
        $channelName = "conversation.{$conversationId}";
        
        $this->pusher->trigger($channelName, 'user-typing', [
            'user_id' => $userId,
            'user_name' => $userName,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Stop typing indicator
     */
    public function broadcastStoppedTyping(int $conversationId, int $userId)
    {
        $channelName = "conversation.{$conversationId}";
        
        $this->pusher->trigger($channelName, 'user-stopped-typing', [
            'user_id' => $userId,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Broadcast to admin channel (system updates)
     */
    public function broadcastToAdmin(string $event, array $data)
    {
        $channelName = "admin.system";
        
        $this->pusher->trigger($channelName, $event, array_merge($data, [
            'timestamp' => now()->toIso8601String(),
        ]));
    }

    /**
     * Get authentication data for Pusher JS client
     */
    public function getAuthenticationData(int $userId)
    {
        return [
            'key' => env('PUSHER_APP_KEY'),
            'cluster' => env('PUSHER_CLUSTER', 'mt'),
            'forceTLS' => true,
            'user_id' => $userId,
            'auth_endpoint' => '/api/v1/broadcasting/auth',
        ];
    }

    /**
     * Authenticate private/presence channels
     */
    public function authenticateChannel($request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return abort(403);
        }

        try {
            $channelName = $request->channel_name;
            $socketId = $request->socket_id;
            $userData = [
                'user_id' => (string) $user->id,
                'user_name' => $user->name,
                'user_email' => $user->email,
                'user_role' => $user->role,
            ];

            // Use presence auth for presence channels, socket auth for private channels
            if (strpos($channelName, 'presence-') === 0) {
                // For presence channels, build auth manually
                $authKey = env('PUSHER_APP_KEY');
                $authSecret = env('PUSHER_APP_SECRET');
                $channelData = json_encode($userData);
                $stringToSign = "$socketId:$channelName:$channelData";
                $auth = hash_hmac('sha256', $stringToSign, $authSecret);
                
                return json_encode([
                    'auth' => "$authKey:$auth",
                    'channel_data' => $channelData
                ]);
            } else {
                // For private channels, use the modern approach
                return json_encode([
                    'auth' => hash_hmac('sha256', "$socketId:$channelName", env('PUSHER_APP_SECRET')),
                    'channel_data' => json_encode($userData),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('WebSocket authentication failed: ' . $e->getMessage());
            return abort(403, 'Authentication failed');
        }
    }

    /**
     * Send direct message via WebSocket
     */
    public function sendDirectMessage(int $toUserId, array $message)
    {
        $channelName = "private-user.{$toUserId}";
        
        $this->pusher->trigger($channelName, 'direct-message', [
            'from_user_id' => $message['from_user_id'],
            'from_user_name' => $message['from_user_name'],
            'content' => $message['content'],
            'created_at' => $message['created_at'],
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Broadcast appointment reminder
     */
    public function broadcastAppointmentReminder(int $patientId, int $doctorId, array $appointment)
    {
        $data = [
            'appointment_id' => $appointment['id'],
            'doctor_name' => $appointment['doctor_name'],
            'scheduled_at' => $appointment['scheduled_at'],
            'minutes_until' => $appointment['minutes_until'],
            'type' => $appointment['type'],
            'timestamp' => now()->toIso8601String(),
        ];
        
        $this->pusher->trigger([
            "user.{$patientId}.reminders",
            "user.{$doctorId}.reminders",
        ], 'appointment-reminder', $data);
    }

    /**
     * Get Pusher instance (for testing/advanced use)
     */
    public function getPusher()
    {
        return $this->pusher;
    }
}
