<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Consultation;

/**
 * Real-time event for consultation status changes
 * 
 * Broadcasts when:
 * - Consultation starts
 * - Consultation ends
 * - Status changes (pending, active, completed, cancelled)
 * 
 * Channels: private-consultations.{consultationId}
 */
class ConsultationStatusBroadcast implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $consultation;
    public $status;
    public $message;

    public function __construct(Consultation $consultation, $status, $message = null)
    {
        $this->consultation = $consultation;
        $this->status = $status;
        $this->message = $message;
    }

    public function broadcastOn(): Channel
    {
        return new PrivateChannel("private-consultations.{$this->consultation->id}");
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->consultation->id,
            'status' => $this->status,
            'message' => $this->message,
            'doctor_id' => $this->consultation->doctor_id,
            'patient_id' => $this->consultation->patient_id,
            'started_at' => $this->consultation->started_at?->toIso8601String(),
            'ended_at' => $this->consultation->ended_at?->toIso8601String(),
            'timestamp' => now()->toIso8601String(),
        ];
    }

    public function broadcastAs()
    {
        return 'consultation-status-changed';
    }
}
