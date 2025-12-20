<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Appointment;

/**
 * Real-time event for appointment updates
 * 
 * Broadcasts when:
 * - Appointment confirmed
 * - Appointment cancelled
 * - Status changes
 * - Reminder sent (1 hour before)
 * 
 * Channels: 
 * - private-appointments.{userId} (for user's appointments)
 * - private-appointments.doctor.{doctorId} (for doctor's appointments)
 */
class AppointmentUpdateBroadcast implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $appointment;
    public $action;
    public $details;

    public function __construct(Appointment $appointment, $action, $details = [])
    {
        $this->appointment = $appointment;
        $this->action = $action;
        $this->details = $details;
    }

    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel("private-appointments.{$this->appointment->patient_id}"),
            new PrivateChannel("private-appointments.doctor.{$this->appointment->doctor_id}"),
        ];

        return $channels;
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->appointment->id,
            'action' => $this->action,
            'patient_id' => $this->appointment->patient_id,
            'doctor_id' => $this->appointment->doctor_id,
            'appointment_date' => $this->appointment->appointment_date,
            'status' => $this->appointment->status,
            'notes' => $this->appointment->notes,
            'details' => $this->details,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    public function broadcastAs()
    {
        return 'appointment-updated';
    }
}
