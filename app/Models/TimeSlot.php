<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TimeSlot extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'doctor_availability_id',
        'date',
        'start_time',
        'end_time',
        'is_available',
        'booked_at',
        'appointment_id',
    ];

    protected $casts = [
        'date' => 'date',
        'is_available' => 'boolean',
        'booked_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function availability()
    {
        return $this->belongsTo(DoctorAvailability::class, 'doctor_availability_id');
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    /**
     * Scopes
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }

    public function scopeForDoctor($query, $doctorId)
    {
        return $query->whereHas('availability', function ($q) use ($doctorId) {
            $q->where('doctor_id', $doctorId);
        });
    }

    public function scopeBooked($query)
    {
        return $query->where('is_available', false)
            ->whereNotNull('appointment_id');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('date', '>=', now()->toDateString());
    }

    /**
     * Methods
     */
    public function book($appointmentId)
    {
        $this->update([
            'is_available' => false,
            'booked_at' => now(),
            'appointment_id' => $appointmentId,
        ]);

        return $this;
    }

    public function release()
    {
        $this->update([
            'is_available' => true,
            'booked_at' => null,
            'appointment_id' => null,
        ]);

        return $this;
    }

    public function getTimeRange()
    {
        return "{$this->start_time} - {$this->end_time}";
    }

    public function getDurationMinutes()
    {
        $start = \Carbon\Carbon::parse($this->start_time);
        $end = \Carbon\Carbon::parse($this->end_time);

        return $end->diffInMinutes($start);
    }

    public function isExpired()
    {
        $slotDateTime = \Carbon\Carbon::parse("{$this->date->format('Y-m-d')} {$this->start_time}");
        return $slotDateTime < now();
    }
}
