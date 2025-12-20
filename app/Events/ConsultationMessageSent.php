<?php

namespace App\Events;

use App\Models\ConsultationMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * ============================================
 * EVENT: CONSULTATION MESSAGE SENT
 * ============================================
 * 
 * Broadcast saat ada message baru dalam konsultasi
 * Real-time sync untuk semua participants
 */
class ConsultationMessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public ConsultationMessage $message;

    public function __construct(ConsultationMessage $message)
    {
        $this->message = $message;
    }

    public function broadcastOn(): array
    {
        // Broadcast ke private channel: consultation.{id}
        // Hanya participants yang bisa dengar
        return [
            new PrivateChannel('consultation.' . $this->message->consultation_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->message->id,
            'consultation_id' => $this->message->consultation_id,
            'sender_id' => $this->message->sender_id,
            'sender_name' => $this->message->sender->name,
            'sender_avatar' => $this->message->sender->avatar_url,
            'message' => $this->message->message,
            'file_url' => $this->message->file_url,
            'file_type' => $this->message->file_type,
            'created_at' => $this->message->created_at->toIso8601String(),
        ];
    }
}
