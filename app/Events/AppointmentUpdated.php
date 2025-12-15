<?php

namespace App\Events;

use App\Models\Appointment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AppointmentUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $appointment;

    public function __construct(Appointment $appointment)
    {
        $this->appointment = $appointment;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->appointment->patient_id . '.appointments'),
            new PrivateChannel('user.' . $this->appointment->doctor_id . '.appointments'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'appointment.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->appointment->id,
            'status' => $this->appointment->status,
            'scheduled_at' => $this->appointment->scheduled_at,
            'doctor_name' => $this->appointment->doctor->name ?? 'Unknown',
            'patient_name' => $this->appointment->patient->name ?? 'Unknown',
            'notes' => $this->appointment->notes,
            'diagnosis' => $this->appointment->diagnosis,
            'updated_at' => $this->appointment->updated_at,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
