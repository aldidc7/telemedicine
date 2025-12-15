<?php

namespace App\Events;

use App\Models\PesanChat;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PesanChatDibaca implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $pesan;
    public $konsultasiId;

    /**
     * Create a new event instance.
     */
    public function __construct(PesanChat $pesan, int $konsultasiId)
    {
        $this->pesan = $pesan;
        $this->konsultasiId = $konsultasiId;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.'.$this->konsultasiId),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'pesan-chat-dibaca';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->pesan->id,
            'dibaca' => $this->pesan->dibaca,
            'dibaca_at' => $this->pesan->dibaca_at?->toIso8601String(),
        ];
    }
}
