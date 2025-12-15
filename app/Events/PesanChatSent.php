<?php

namespace App\Events;

use App\Models\PesanChat;
use App\Models\Konsultasi;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PesanChatSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $pesan;
    public $konsultasi;
    public $pengirimNama;

    /**
     * Create a new event instance.
     */
    public function __construct(PesanChat $pesan, Konsultasi $konsultasi)
    {
        $this->pesan = $pesan;
        $this->konsultasi = $konsultasi;
        
        // Load pengirim info
        $pengirim = $pesan->pengirim;
        $this->pengirimNama = $pengirim->name ?? 'Unknown';
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.'.$this->konsultasi->id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'pesan-chat-sent';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->pesan->id,
            'konsultasi_id' => $this->konsultasi->id,
            'pengirim_id' => $this->pesan->pengirim_id,
            'pengirim_nama' => $this->pengirimNama,
            'pesan' => $this->pesan->pesan,
            'tipe_pesan' => $this->pesan->tipe_pesan,
            'url_file' => $this->pesan->url_file,
            'dibaca' => $this->pesan->dibaca,
            'created_at' => $this->pesan->created_at?->toIso8601String(),
        ];
    }
}
