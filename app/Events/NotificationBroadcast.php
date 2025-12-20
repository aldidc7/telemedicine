<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Notification;

/**
 * Real-time broadcast event for notification delivery
 * 
 * Broadcasts notifications to users via WebSocket
 * Channels: private-notifications.{userId}
 * 
 * Usage:
 * NotificationBroadcast::dispatch($notification);
 */
class NotificationBroadcast implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Notification
     */
    public $notification;

    /**
     * Create a new event instance
     */
    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
    }

    /**
     * Get the channels the event should broadcast on
     */
    public function broadcastOn(): Channel
    {
        return new PrivateChannel("private-notifications.{$this->notification->user_id}");
    }

    /**
     * Get the data to broadcast
     */
    public function broadcastWith()
    {
        return [
            'id' => $this->notification->id,
            'user_id' => $this->notification->user_id,
            'type' => $this->notification->type,
            'title' => $this->notification->title,
            'message' => $this->notification->message,
            'data' => $this->notification->data,
            'channel' => $this->notification->channel,
            'action_url' => $this->notification->action_url,
            'is_read' => $this->notification->is_read,
            'created_at' => $this->notification->created_at->toIso8601String(),
        ];
    }

    /**
     * The event's broadcast name
     */
    public function broadcastAs()
    {
        return 'general-notification';
    }
}
