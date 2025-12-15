<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'scheduled_at',
        'started_at',
        'ended_at',
        'status',
        'type',
        'reason',
        'notes',
        'consultation_link',
        'duration_minutes',
        'price',
        'payment_status',
        'confirmed_at',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relasi ke patient (user)
     */
    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    /**
     * Relasi ke doctor (user)
     */
    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    /**
     * Check if appointment is upcoming
     */
    public function isUpcoming(): bool
    {
        return $this->scheduled_at->isFuture() && in_array($this->status, ['pending', 'confirmed']);
    }

    /**
     * Check if appointment can be modified
     */
    public function canBeModified(): bool
    {
        return in_array($this->status, ['pending', 'confirmed']) && 
               $this->scheduled_at->diffInHours(now()) > 1;
    }

    /**
     * Check if appointment can be cancelled
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'confirmed', 'in_progress']) && 
               $this->scheduled_at->isFuture();
    }

    /**
     * Confirm appointment
     */
    public function confirm()
    {
        $this->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);
    }

    /**
     * Reject appointment
     */
    public function reject($reason)
    {
        $this->update([
            'status' => 'rejected',
            'cancellation_reason' => $reason,
            'cancelled_at' => now(),
        ]);
    }

    /**
     * Cancel appointment
     */
    public function cancel($reason)
    {
        $this->update([
            'status' => 'cancelled',
            'cancellation_reason' => $reason,
            'cancelled_at' => now(),
        ]);
    }

    /**
     * Start appointment
     */
    public function start()
    {
        $this->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);
    }

    /**
     * Complete appointment
     */
    public function complete()
    {
        $this->update([
            'status' => 'completed',
            'ended_at' => now(),
        ]);
    }

    /**
     * Get appointment duration
     */
    public function getDuration()
    {
        if ($this->started_at && $this->ended_at) {
            return $this->ended_at->diffInMinutes($this->started_at);
        }
        return null;
    }

    /**
     * Check if appointment is past
     */
    public function isPast(): bool
    {
        return $this->scheduled_at->isPast();
    }

    /**
     * Scope untuk upcoming appointments
     */
    public function scopeUpcoming($query)
    {
        return $query->where('scheduled_at', '>', now())
            ->whereIn('status', ['pending', 'confirmed'])
            ->orderBy('scheduled_at');
    }

    /**
     * Scope untuk past appointments
     */
    public function scopePast($query)
    {
        return $query->where('scheduled_at', '<', now())
            ->orderBy('scheduled_at', 'desc');
    }

    /**
     * Scope untuk patient
     */
    public function scopeForPatient($query, $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    /**
     * Scope untuk doctor
     */
    public function scopeForDoctor($query, $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    /**
     * Scope untuk specific date
     */
    public function scopeOnDate($query, $date)
    {
        return $query->whereDate('scheduled_at', $date);
    }
}
