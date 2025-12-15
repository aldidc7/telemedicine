<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Prescription extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'appointment_id',
        'doctor_id',
        'patient_id',
        'medications',
        'notes',
        'instructions',
        'status',
        'issued_at',
        'expires_at',
        'doctor_notes',
        'patient_acknowledged',
        'acknowledged_at',
    ];

    protected $casts = [
        'medications' => 'array',
        'issued_at' => 'datetime',
        'expires_at' => 'datetime',
        'acknowledged_at' => 'datetime',
        'patient_acknowledged' => 'boolean',
    ];

    /**
     * Relasi ke appointment
     */
    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    /**
     * Relasi ke doctor (user)
     */
    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    /**
     * Relasi ke patient (user)
     */
    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    /**
     * Check if prescription is still active
     */
    public function isActive(): bool
    {
        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }
        return $this->status === 'active';
    }

    /**
     * Check if prescription is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Mark prescription as acknowledged by patient
     */
    public function acknowledge()
    {
        $this->update([
            'patient_acknowledged' => true,
            'acknowledged_at' => now(),
        ]);
    }

    /**
     * Mark prescription as completed
     */
    public function markCompleted()
    {
        $this->update(['status' => 'completed']);
    }

    /**
     * Mark prescription as expired
     */
    public function markExpired()
    {
        $this->update(['status' => 'expired']);
    }

    /**
     * Get medication count
     */
    public function getMedicationCount(): int
    {
        return count($this->medications ?? []);
    }

    /**
     * Get total quantity needed
     */
    public function getTotalQuantity(): int
    {
        $total = 0;
        foreach ($this->medications ?? [] as $med) {
            $total += intval($med['quantity'] ?? 0);
        }
        return $total;
    }

    /**
     * Scope: get active prescriptions
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Scope: get expired prescriptions
     */
    public function scopeExpired($query)
    {
        return $query->where(function ($q) {
            $q->where('status', 'expired')
                ->orWhere('expires_at', '<=', now());
        });
    }

    /**
     * Scope: for specific patient
     */
    public function scopeForPatient($query, $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    /**
     * Scope: for specific doctor
     */
    public function scopeForDoctor($query, $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    /**
     * Scope: unacknowledged prescriptions
     */
    public function scopeUnacknowledged($query)
    {
        return $query->where('patient_acknowledged', false);
    }

    /**
     * Scope: by appointment
     */
    public function scopeByAppointment($query, $appointmentId)
    {
        return $query->where('appointment_id', $appointmentId);
    }
}
