<?php

namespace App\Events;

use App\Models\Konsultasi;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithBroadcasting;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * ============================================
 * EVENT: APPOINTMENT REMINDER NOTIFICATION
 * ============================================
 * 
 * Broadcast appointment reminder to user
 */
class AppointmentReminderNotification implements ShouldBroadcast
{
    use Dispatchable, InteractsWithBroadcasting, SerializesModels;

    public User $user;
    public string $title;
    public string $message;
    public Konsultasi $appointment;

    public function __construct(User $user, string $title, string $message, Konsultasi $appointment)
    {
        $this->user = $user;
        $this->title = $title;
        $this->message = $message;
        $this->appointment = $appointment;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('user.' . $this->user->id);
    }

    public function broadcastAs(): string
    {
        return 'appointment-reminder';
    }

    public function broadcastWith(): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'appointment' => [
                'id' => $this->appointment->id,
                'doctor' => $this->appointment->dokter->user->name,
                'patient' => $this->appointment->pasien->user->name,
                'appointment_time' => $this->appointment->appointment_time,
            ],
        ];
    }
}
