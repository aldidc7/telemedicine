<?php

namespace App\Events;

use App\Models\Konsultasi;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConsultationEnded implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Konsultasi $konsultasi;
    public string $status;

    /**
     * Create a new event instance.
     */
    public function __construct(Konsultasi $konsultasi)
    {
        $this->konsultasi = $konsultasi;
        $this->status = 'ended';
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('consultation.' . $this->konsultasi->id),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'konsultasi_id' => $this->konsultasi->id,
            'status' => $this->status,
            'dokter_id' => $this->konsultasi->dokter_id,
            'pasien_id' => $this->konsultasi->pasien_id,
            'waktu_selesai' => $this->konsultasi->waktu_selesai,
            'durasi' => $this->konsultasi->durasi,
        ];
    }

    /**
     * Get the event name.
     */
    public function broadcastAs(): string
    {
        return 'consultation.ended';
    }
}
