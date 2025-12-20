<?php

namespace App\Services\Doctor;

use App\Models\DoctorAvailability;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * ============================================
 * SERVICE: DOCTOR AVAILABILITY
 * ============================================
 * 
 * Business logic untuk doctor schedule management
 * - Set/update availability
 * - Get available slots
 * - Check availability for specific date
 */
class DoctorAvailabilityService
{
    /**
     * Set availability untuk dokter untuk satu hari
     * 
     * @param int $doctorId
     * @param array $data ['day_of_week', 'start_time', 'end_time', 'break_start', 'break_end']
     * @return DoctorAvailability
     */
    public function setAvailability(int $doctorId, array $data): DoctorAvailability
    {
        // Validate time format
        if (!DoctorAvailability::isValidTimeFormat($data['start_time'] ?? '')) {
            throw new \InvalidArgumentException('Invalid start_time format. Use HH:MM');
        }
        if (!DoctorAvailability::isValidTimeFormat($data['end_time'] ?? '')) {
            throw new \InvalidArgumentException('Invalid end_time format. Use HH:MM');
        }

        return DoctorAvailability::updateOrCreate(
            [
                'doctor_id' => $doctorId,
                'day_of_week' => $data['day_of_week'],
            ],
            [
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'slot_duration' => $data['slot_duration'] ?? 30,
                'max_patients_per_slot' => $data['max_patients_per_slot'] ?? 1,
                'is_active' => $data['is_active'] ?? true,
                'notes' => $data['notes'] ?? null,
            ]
        );
    }

    /**
     * Bulk set availability untuk semua hari
     * 
     * @param int $doctorId
     * @param array $schedules Array of schedules
     * @return int Jumlah availability yang dibuat/diupdate
     */
    public function bulkSetAvailability(int $doctorId, array $schedules): int
    {
        $count = 0;

        foreach ($schedules as $schedule) {
            $this->setAvailability($doctorId, $schedule);
            $count++;
        }

        return $count;
    }

    /**
     * Get all availability untuk dokter
     * 
     * @param int $doctorId
     * @param bool $onlyActive
     * @return Collection
     */
    public function getAvailability(int $doctorId, bool $onlyActive = true): Collection
    {
        $query = DoctorAvailability::forDoctor($doctorId);

        if ($onlyActive) {
            $query->active();
        }

        return $query->orderBy('day_of_week')->get();
    }

    /**
     * Get availability untuk hari tertentu
     * 
     * @param int $doctorId
     * @param int $dayOfWeek (0-6, 0=Sunday)
     * @return DoctorAvailability|null
     */
    public function getAvailabilityForDay(int $doctorId, int $dayOfWeek): ?DoctorAvailability
    {
        return DoctorAvailability::forDoctor($doctorId)
            ->forDay($dayOfWeek)
            ->active()
            ->first();
    }

    /**
     * Get available time slots untuk date range
     * 
     * @param int $doctorId
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array Format: [['date' => '2024-12-21', 'slots' => ['09:00', '09:30', ...]]]
     */
    public function getAvailableSlots(int $doctorId, Carbon $startDate, Carbon $endDate): array
    {
        $slots = [];

        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            $dayOfWeek = $date->dayOfWeekIso; // 1=Monday, 7=Sunday
            $dayOfWeek = $dayOfWeek === 7 ? 0 : $dayOfWeek; // Convert Sunday to 0

            $availability = $this->getAvailabilityForDay($doctorId, $dayOfWeek);

            if ($availability && $availability->is_active) {
                $daySlots = $availability->generateTimeSlots();

                if (!empty($daySlots)) {
                    $slots[] = [
                        'date' => $date->format('Y-m-d'),
                        'day_name' => $availability->getDayLabel(),
                        'slots' => $daySlots,
                        'slot_duration' => $availability->slot_duration ?? 30,
                    ];
                }
            }
        }

        return $slots;
    }

    /**
     * Toggle availability status (enable/disable)
     * 
     * @param int $availabilityId
     * @param int $doctorId
     * @param bool $active
     * @return DoctorAvailability
     * @throws \Exception
     */
    public function toggleAvailability(int $availabilityId, int $doctorId, bool $active): DoctorAvailability
    {
        $availability = DoctorAvailability::find($availabilityId);

        if (!$availability || $availability->doctor_id !== $doctorId) {
            throw new \Exception('Availability not found or unauthorized');
        }

        $availability->update(['is_active' => $active]);

        return $availability;
    }

    /**
     * Delete availability
     * 
     * @param int $availabilityId
     * @param int $doctorId
     * @return bool
     * @throws \Exception
     */
    public function deleteAvailability(int $availabilityId, int $doctorId): bool
    {
        $availability = DoctorAvailability::find($availabilityId);

        if (!$availability || $availability->doctor_id !== $doctorId) {
            throw new \Exception('Availability not found or unauthorized');
        }

        return $availability->delete();
    }

    /**
     * Check apakah dokter available pada tanggal/jam tertentu
     * 
     * @param int $doctorId
     * @param Carbon $dateTime
     * @return bool
     */
    public function isAvailable(int $doctorId, Carbon $dateTime): bool
    {
        $dayOfWeek = $dateTime->dayOfWeekIso;
        $dayOfWeek = $dayOfWeek === 7 ? 0 : $dayOfWeek;

        $availability = $this->getAvailabilityForDay($doctorId, $dayOfWeek);

        if (!$availability) {
            return false;
        }

        $startTime = Carbon::createFromFormat('H:i:s', $availability->start_time);
        $endTime = Carbon::createFromFormat('H:i:s', $availability->end_time);
        $checkTime = $dateTime->copy()->setTimeFromTimeString('H:i');

        return $checkTime >= $startTime && $checkTime < $endTime;
    }

    /**
     * Get summary statistics untuk doctor
     * 
     * @param int $doctorId
     * @return array
     */
    public function getStatistics(int $doctorId): array
    {
        $availabilities = $this->getAvailability($doctorId);

        $totalHours = 0;
        foreach ($availabilities as $avail) {
            $start = Carbon::createFromFormat('H:i:s', $avail->start_time);
            $end = Carbon::createFromFormat('H:i:s', $avail->end_time);
            $totalHours += $end->diffInHours($start);
        }

        return [
            'total_days' => $availabilities->count(),
            'total_hours_per_week' => $totalHours,
            'avg_slot_duration' => (int) $availabilities->avg('slot_duration'),
            'active_count' => $availabilities->where('is_active', true)->count(),
        ];
    }
}
