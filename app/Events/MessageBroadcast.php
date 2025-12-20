<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Real-time event for message delivery and read receipts
 * 
 * Broadcasts when:
 * - New message sent
 * - Message read
 * - Typing indicator
 * 
 * Channels: private-conversation.{conversationId}
 */
class MessageBroadcast implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $conversationId;
    public $type; // 'sent', 'read', 'typing'
    public $data;

    public function __construct($conversationId, $type, $data = [])
    {
        $this->conversationId = $conversationId;
        $this->type = $type;
        $this->data = $data;
    }

    public function broadcastOn(): Channel
    {
        return new PrivateChannel("private-conversation.{$this->conversationId}");
    }

    public function broadcastWith()
    {
        return [
            'conversation_id' => $this->conversationId,
            'type' => $this->type,
            'data' => $this->data,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    public function broadcastAs()
    {
        return "message-{$this->type}";
    }
}
