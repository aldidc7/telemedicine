<?php

namespace App\Events;

use App\Models\PesanChat;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event untuk menandai pesan sebagai telah dibaca
 * Dipicu ketika pengguna membuka/membaca pesan
 */
class MessageRead implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public PesanChat $message;
    public int $consultationId;
    public int $readById;

    /**
     * Create a new event instance.
     */
    public function __construct(PesanChat $message, int $consultationId, int $readById)
    {
        $this->message = $message;
        $this->consultationId = $consultationId;
        $this->readById = $readById;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('consultation.' . $this->consultationId),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->message->id,
            'consultation_id' => $this->consultationId,
            'read_by' => $this->readById,
            'read_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Get the event name.
     */
    public function broadcastAs(): string
    {
        return 'message.read';
    }
}
