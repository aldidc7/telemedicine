<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DoctorAvailability extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'doctor_id',
        'day_of_week',
        'start_time',
        'end_time',
        'break_start',
        'break_end',
        'slot_duration_minutes',
        'max_appointments_per_day',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Day of week constants (0 = Sunday, 6 = Saturday)
    const SUNDAY = 0;
    const MONDAY = 1;
    const TUESDAY = 2;
    const WEDNESDAY = 3;
    const THURSDAY = 4;
    const FRIDAY = 5;
    const SATURDAY = 6;

    public static $dayNames = [
        self::SUNDAY => 'Minggu',
        self::MONDAY => 'Senin',
        self::TUESDAY => 'Selasa',
        self::WEDNESDAY => 'Rabu',
        self::THURSDAY => 'Kamis',
        self::FRIDAY => 'Jumat',
        self::SATURDAY => 'Sabtu',
    ];

    /**
     * Relationships
     */
    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function timeSlots()
    {
        return $this->hasMany(TimeSlot::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForDoctor($query, $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    public function scopeForDayOfWeek($query, $dayOfWeek)
    {
        return $query->where('day_of_week', $dayOfWeek);
    }

    /**
     * Methods
     */
    public function getDayName()
    {
        return self::$dayNames[$this->day_of_week] ?? 'Unknown';
    }

    public function isAvailableOn($date)
    {
        $dayOfWeek = $date->dayOfWeek;
        return $this->day_of_week === $dayOfWeek && $this->is_active;
    }

    public function hasBreak()
    {
        return !is_null($this->break_start) && !is_null($this->break_end);
    }

    public function getSlots($date)
    {
        if (!$this->isAvailableOn($date)) {
            return [];
        }

        $slots = [];
        $startTime = \Carbon\Carbon::parse("{$date->format('Y-m-d')} {$this->start_time}");
        $endTime = \Carbon\Carbon::parse("{$date->format('Y-m-d')} {$this->end_time}");

        while ($startTime < $endTime) {
            $slotEnd = $startTime->copy()->addMinutes($this->slot_duration_minutes);

            // Skip if in break time
            if ($this->hasBreak()) {
                $breakStart = \Carbon\Carbon::parse("{$date->format('Y-m-d')} {$this->break_start}");
                $breakEnd = \Carbon\Carbon::parse("{$date->format('Y-m-d')} {$this->break_end}");

                if ($startTime >= $breakStart && $startTime < $breakEnd) {
                    $startTime = $breakEnd->copy();
                    continue;
                }
            }

            $slots[] = [
                'start' => $startTime->copy(),
                'end' => $slotEnd->copy(),
                'duration' => $this->slot_duration_minutes,
            ];

            $startTime = $slotEnd;
        }

        return $slots;
    }

    public function getAvailableSlots($date, $limit = null)
    {
        $allSlots = $this->getSlots($date);
        
        // Get booked appointments for this date
        $booked = Appointment::forDoctor($this->doctor_id)
            ->whereDate('scheduled_at', $date)
            ->where('status', '!=', Appointment::STATUS_CANCELLED)
            ->pluck('scheduled_at')
            ->toArray();

        $available = [];
        foreach ($allSlots as $slot) {
            $isBooked = false;
            foreach ($booked as $appointmentTime) {
                $appt = \Carbon\Carbon::parse($appointmentTime);
                if ($appt >= $slot['start'] && $appt < $slot['end']) {
                    $isBooked = true;
                    break;
                }
            }

            if (!$isBooked) {
                $available[] = $slot;
            }
        }

        if ($limit) {
            $available = array_slice($available, 0, $limit);
        }

        return $available;
    }

    public function formatTimeRange()
    {
        return "{$this->start_time} - {$this->end_time}";
    }
}
