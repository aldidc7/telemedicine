<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RatingReceived implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $doctorId;
    public $ratingData;

    public function __construct(int $doctorId, array $ratingData)
    {
        $this->doctorId = $doctorId;
        $this->ratingData = $ratingData;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('doctor.' . $this->doctorId . '.ratings'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'rating.received';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->ratingData['id'] ?? null,
            'rating' => $this->ratingData['rating'],
            'comment' => $this->ratingData['comment'] ?? '',
            'patient_name' => $this->ratingData['patient_name'] ?? 'Anonymous',
            'created_at' => $this->ratingData['created_at'] ?? now(),
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
