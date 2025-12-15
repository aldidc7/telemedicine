<?php

namespace App\Events;

use App\Models\Prescription;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PrescriptionCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $prescription;
    public $patientId;

    public function __construct(Prescription $prescription)
    {
        $this->prescription = $prescription;
        $this->patientId = $prescription->patient_id;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->patientId . '.prescriptions'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'prescription.created';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->prescription->id,
            'doctor_name' => $this->prescription->doctor->name ?? 'Unknown',
            'medication_count' => $this->prescription->medications()->count() ?? 0,
            'diagnosis' => $this->prescription->diagnosis,
            'notes' => $this->prescription->notes,
            'created_at' => $this->prescription->created_at,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
