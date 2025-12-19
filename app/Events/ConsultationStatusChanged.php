<?php

namespace App\Events;

use App\Models\Konsultasi;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event untuk status perubahan konsultasi
 * Dipicu ketika status konsultasi berubah (pending -> ongoing -> completed, etc)
 */
class ConsultationStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Konsultasi $konsultasi;
    public string $oldStatus;
    public string $newStatus;

    /**
     * Create a new event instance.
     */
    public function __construct(Konsultasi $konsultasi, string $oldStatus, string $newStatus)
    {
        $this->konsultasi = $konsultasi;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }

    /**
     * Get the channels the event should broadcast on.
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
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'dokter_id' => $this->konsultasi->dokter_id,
            'pasien_id' => $this->konsultasi->pasien_id,
            'status_changed_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Get the event name.
     */
    public function broadcastAs(): string
    {
        return 'consultation.status_changed';
    }
}
