<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\User;
use App\Events\AppointmentUpdated;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class AppointmentService
{
    protected $concurrentAccessService;

    public function __construct(ConcurrentAccessService $concurrentAccessService)
    {
        $this->concurrentAccessService = $concurrentAccessService;
    }

    /**
     * Book new appointment (with concurrent access control)
     */
    public function bookAppointment(
        int $patientId,
        int $doctorId,
        string $scheduledAt,
        string $type,
        ?string $reason = null,
        ?float $price = null
    ): Appointment {
        // Use atomic operation dengan pessimistic locking
        $appointment = $this->concurrentAccessService->bookAppointmentAtomic(
            $patientId,
            $doctorId,
            $scheduledAt,
            $type,
            $reason
        );

        // Send notification ke doctor asynchronously
        try {
            $doctor = $appointment->doctor;
            $patient = $appointment->patient;
            (new NotificationService())->notifyAppointmentCreated(
                $doctorId,
                $appointment->id,
                $patient->name,
                $scheduledAt
            );
        } catch (\Exception $e) {
            \Log::warning('Failed to create appointment notification: ' . $e->getMessage());
            // Continue - appointment created, notification failure is non-critical
        }

        return $appointment;
    }

    /**
     * Get available slots untuk doctor di tanggal tertentu (with caching)
     */
    public function getAvailableSlots(int $doctorId, string $date, int $slotDurationMinutes = 30): array
    {
        // Use cache with 15 minute TTL
        $cacheKey = "appointments:slots:{$doctorId}:" . Carbon::parse($date)->format('Y-m-d');
        
        return Cache::remember($cacheKey, 900, function () use ($doctorId, $date, $slotDurationMinutes) {
            $date = Carbon::parse($date)->startOfDay();

            // Get working hours (9 AM - 5 PM default)
            $workStart = $date->clone()->setHour(9);
            $workEnd = $date->clone()->setHour(17);

            // Get booked appointments
            $bookedAppointments = Appointment::where('doctor_id', $doctorId)
                ->whereDate('scheduled_at', $date)
                ->whereIn('status', ['pending', 'confirmed', 'in_progress'])
                ->pluck('scheduled_at')
                ->toArray();

            // Generate available slots
            $slots = [];
            $currentTime = $workStart;

            while ($currentTime->isBefore($workEnd)) {
                $slotEnd = $currentTime->clone()->addMinutes($slotDurationMinutes);

                // Check if slot is free
                $isFree = true;
                foreach ($bookedAppointments as $bookedTime) {
                    $booked = Carbon::parse($bookedTime);
                    if ($currentTime->lte($booked) && $slotEnd->gt($booked)) {
                        $isFree = false;
                        break;
                    }
                }

                if ($isFree) {
                    $slots[] = $currentTime->format('Y-m-d H:i:s');
                }

                $currentTime->addMinutes($slotDurationMinutes);
            }

            return $slots;
        });
    }

    /**
     * Confirm appointment (by doctor) - dengan concurrent access control
     */
    public function confirmAppointment(int $appointmentId, int $doctorId): Appointment
    {
        // Use atomic operation untuk status update
        $appointment = $this->concurrentAccessService->updateAppointmentStatusAtomic(
            $appointmentId,
            'confirmed',
            $doctorId,
            'dokter'
        );

        // Broadcast appointment update via WebSocket
        try {
            $appointment->load(['doctor', 'patient']);
            broadcast(new AppointmentUpdated($appointment));
        } catch (\Exception $e) {
            \Log::warning('Failed to broadcast appointment update: ' . $e->getMessage());
        }

        // Send notification ke patient
        try {
            (new NotificationService())->notifyAppointmentConfirmed(
                $appointment->patient_id,
                $appointmentId,
                $appointment->doctor->name,
                $appointment->scheduled_at
            );
        } catch (\Exception $e) {
            \Log::warning('Failed to send confirmation notification: ' . $e->getMessage());
        }

        return $appointment;
    }

    /**
     * Reject appointment (by doctor) - dengan concurrent access control
     */
    public function rejectAppointment(int $appointmentId, int $doctorId, string $reason): Appointment
    {
        // Use atomic operation untuk status update
        $appointment = $this->concurrentAccessService->updateAppointmentStatusAtomic(
            $appointmentId,
            'rejected',
            $doctorId,
            'dokter'
        );

        // Send notification ke patient
        try {
            (new NotificationService())->notifyAppointmentRejected(
                $appointment->patient_id,
                $appointmentId,
                $reason
            );
        } catch (\Exception $e) {
            \Log::warning('Failed to send rejection notification: ' . $e->getMessage());
        }

        return $appointment;
    }

    /**
     * Cancel appointment
     */
    public function cancelAppointment(int $appointmentId, int $userId, string $reason): Appointment
    {
        $appointment = Appointment::findOrFail($appointmentId);

        // Validasi user berhak cancel (patient atau doctor)
        if ($appointment->patient_id !== $userId && $appointment->doctor_id !== $userId) {
            throw new \Exception('Anda tidak berhak cancel appointment ini');
        }

        if (!$appointment->canBeCancelled()) {
            throw new \Exception('Appointment tidak bisa di-cancel');
        }

        $appointment->cancel($reason);

        // Send notification ke pihak lain
        $otherUserId = $appointment->patient_id === $userId 
            ? $appointment->doctor_id 
            : $appointment->patient_id;

        (new NotificationService())->notifyAppointmentCancelled(
            $otherUserId,
            $appointmentId,
            $reason
        );

        return $appointment;
    }

    /**
     * Reschedule appointment
     */
    public function rescheduleAppointment(int $appointmentId, string $newScheduledAt, int $userId): Appointment
    {
        $appointment = Appointment::findOrFail($appointmentId);

        // Validasi patient berhak reschedule
        if ($appointment->patient_id !== $userId) {
            throw new \Exception('Hanya patient dapat reschedule appointment');
        }

        if (!$appointment->canBeModified()) {
            throw new \Exception('Appointment tidak bisa di-reschedule');
        }

        // Validasi tidak ada konflik
        $conflictExists = Appointment::where('doctor_id', $appointment->doctor_id)
            ->where('scheduled_at', $newScheduledAt)
            ->where('id', '!=', $appointmentId)
            ->whereIn('status', ['pending', 'confirmed', 'in_progress'])
            ->exists();

        if ($conflictExists) {
            throw new \Exception('Doctor sudah memiliki appointment pada waktu baru');
        }

        $oldScheduledAt = $appointment->scheduled_at;
        $appointment->update(['scheduled_at' => $newScheduledAt, 'status' => 'pending']);

        // Broadcast appointment update via WebSocket
        try {
            $appointment->load(['doctor', 'patient']);
            broadcast(new AppointmentUpdated($appointment));
        } catch (\Exception $e) {
            \Log::warning('Failed to broadcast appointment update: ' . $e->getMessage());
        }

        // Send notification ke doctor
        (new NotificationService())->notifyAppointmentRescheduled(
            $appointment->doctor_id,
            $appointmentId,
            $oldScheduledAt,
            $newScheduledAt
        );

        return $appointment;
    }

    /**
     * Start appointment (by doctor)
     */
    public function startAppointment(int $appointmentId, int $doctorId): Appointment
    {
        $appointment = Appointment::findOrFail($appointmentId);

        if ($appointment->doctor_id !== $doctorId) {
            throw new \Exception('Hanya doctor dapat start appointment');
        }

        if ($appointment->status !== 'confirmed') {
            throw new \Exception('Hanya confirmed appointment yang dapat dimulai');
        }

        $appointment->start();

        // Broadcast appointment update via WebSocket
        try {
            $appointment->load(['doctor', 'patient']);
            broadcast(new AppointmentUpdated($appointment));
        } catch (\Exception $e) {
            \Log::warning('Failed to broadcast appointment update: ' . $e->getMessage());
        }

        // Send notification ke patient
        (new NotificationService())->notifyAppointmentStarted(
            $appointment->patient_id,
            $appointmentId,
            $appointment->doctor->name
        );

        return $appointment;
    }

    /**
     * End appointment (by doctor)
     */
    public function endAppointment(int $appointmentId, int $doctorId, ?string $notes = null): Appointment
    {
        $appointment = Appointment::findOrFail($appointmentId);

        if ($appointment->doctor_id !== $doctorId) {
            throw new \Exception('Hanya doctor dapat end appointment');
        }

        if ($appointment->status !== 'in_progress') {
            throw new \Exception('Hanya in-progress appointment yang dapat di-end');
        }

        $appointment->complete();

        if ($notes) {
            $appointment->update(['notes' => $notes]);
        }

        // Broadcast appointment update via WebSocket
        try {
            $appointment->load(['doctor', 'patient']);
            broadcast(new AppointmentUpdated($appointment));
        } catch (\Exception $e) {
            \Log::warning('Failed to broadcast appointment update: ' . $e->getMessage());
        }

        // Send notification ke patient
        (new NotificationService())->notifyAppointmentCompleted(
            $appointment->patient_id,
            $appointmentId
        );

        return $appointment;
    }

    /**
     * Get patient appointments
     */
    public function getPatientAppointments(int $patientId, ?string $status = null, int $page = 1, int $perPage = 15)
    {
        $query = Appointment::forPatient($patientId)->with(['doctor', 'patient']);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->orderBy('scheduled_at', 'desc')->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Get doctor appointments
     */
    public function getDoctorAppointments(int $doctorId, ?string $status = null, int $page = 1, int $perPage = 15)
    {
        $query = Appointment::forDoctor($doctorId)->with(['doctor', 'patient']);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->orderBy('scheduled_at', 'desc')->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Get appointment by ID with relationships
     */
    public function getAppointmentDetail(int $appointmentId): Appointment
    {
        return Appointment::with(['doctor', 'patient'])->findOrFail($appointmentId);
    }

    /**
     * Get upcoming appointments for doctor (for availability view)
     */
    public function getUpcomingAppointments(int $doctorId, int $days = 7): Collection
    {
        return Appointment::forDoctor($doctorId)
            ->with(['patient', 'doctor'])  // Eager load to prevent N+1 queries
            ->upcoming()
            ->where('scheduled_at', '<=', now()->addDays($days))
            ->get();
    }

    /**
     * Get appointments statistics
     */
    public function getAppointmentStats(int $userId, string $role): array
    {
        if ($role === 'pasien') {
            $query = Appointment::where('patient_id', $userId);
        } else {
            $query = Appointment::where('doctor_id', $userId);
        }

        return [
            'total' => $query->count(),
            'pending' => $query->where('status', 'pending')->count(),
            'confirmed' => $query->where('status', 'confirmed')->count(),
            'completed' => $query->where('status', 'completed')->count(),
            'cancelled' => $query->where('status', 'cancelled')->count(),
            'rejected' => $query->where('status', 'rejected')->count(),
            'upcoming' => $query->upcoming()->count(),
            'past' => $query->past()->count(),
        ];
    }

    /**
     * Get today's appointments untuk dashboard
     */
    public function getTodayAppointments(int $userId, string $role): Collection
    {
        if ($role === 'pasien') {
            $query = Appointment::where('patient_id', $userId);
        } else {
            $query = Appointment::where('doctor_id', $userId);
        }

        return $query->onDate(today())->orderBy('scheduled_at')->get();
    }

    /**
     * Search appointments dengan filter
     */
    public function searchAppointments(
        int $userId,
        string $role,
        ?string $search = null,
        ?string $status = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        int $page = 1,
        int $perPage = 15
    ) {
        if ($role === 'pasien') {
            $query = Appointment::where('patient_id', $userId)->with(['doctor']);
        } else {
            $query = Appointment::where('doctor_id', $userId)->with(['patient']);
        }

        if ($search) {
            $query->where(function ($q) use ($search, $role) {
                if ($role === 'pasien') {
                    $q->whereHas('doctor', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })->orWhere('reason', 'like', "%{$search}%");
                } else {
                    $q->whereHas('patient', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })->orWhere('reason', 'like', "%{$search}%");
                }
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($dateFrom) {
            $query->where('scheduled_at', '>=', Carbon::parse($dateFrom)->startOfDay());
        }

        if ($dateTo) {
            $query->where('scheduled_at', '<=', Carbon::parse($dateTo)->endOfDay());
        }

        return $query->orderBy('scheduled_at', 'desc')->paginate($perPage, ['*'], 'page', $page);
    }
}
