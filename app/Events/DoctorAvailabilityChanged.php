<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DoctorAvailabilityChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $doctorId;
    public $available;

    public function __construct(int $doctorId, bool $available)
    {
        $this->doctorId = $doctorId;
        $this->available = $available;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('doctor.' . $this->doctorId . '.availability'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'doctor.availability.changed';
    }

    public function broadcastWith(): array
    {
        return [
            'doctor_id' => $this->doctorId,
            'available' => $this->available,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
