<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\Paginator;
use Carbon\Carbon;

class AppointmentService
{
    /**
     * Book new appointment
     */
    public function bookAppointment(
        int $patientId,
        int $doctorId,
        string $scheduledAt,
        string $type,
        ?string $reason = null,
        ?float $price = null
    ): Appointment {
        // Validasi doctor exists dan adalah dokter
        $doctor = User::findOrFail($doctorId);
        if ($doctor->role !== 'dokter') {
            throw new \Exception('User harus dokter');
        }

        // Validasi patient exists
        $patient = User::findOrFail($patientId);
        if ($patient->role !== 'pasien') {
            throw new \Exception('User harus pasien');
        }

        // Validasi tidak ada appointment konflik
        $existingAppointment = Appointment::where('doctor_id', $doctorId)
            ->where('scheduled_at', $scheduledAt)
            ->whereIn('status', ['pending', 'confirmed', 'in_progress'])
            ->exists();

        if ($existingAppointment) {
            throw new \Exception('Doctor sudah memiliki appointment pada waktu tersebut');
        }

        // Create appointment
        $appointment = Appointment::create([
            'patient_id' => $patientId,
            'doctor_id' => $doctorId,
            'scheduled_at' => $scheduledAt,
            'type' => $type,
            'reason' => $reason,
            'price' => $price,
            'status' => 'pending',
            'payment_status' => 'pending',
        ]);

        // Send notification ke doctor
        (new NotificationService())->notifyAppointmentCreated(
            $doctorId,
            $appointment->id,
            $patient->name,
            $scheduledAt
        );

        return $appointment;
    }

    /**
     * Get available slots untuk doctor di tanggal tertentu
     */
    public function getAvailableSlots(int $doctorId, string $date, int $slotDurationMinutes = 30): array
    {
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
    }

    /**
     * Confirm appointment (by doctor)
     */
    public function confirmAppointment(int $appointmentId, int $doctorId): Appointment
    {
        $appointment = Appointment::findOrFail($appointmentId);

        if ($appointment->doctor_id !== $doctorId) {
            throw new \Exception('Hanya doctor dapat confirm appointment');
        }

        if ($appointment->status !== 'pending') {
            throw new \Exception('Hanya appointment pending yang dapat di-confirm');
        }

        $appointment->confirm();

        // Send notification ke patient
        (new NotificationService())->notifyAppointmentConfirmed(
            $appointment->patient_id,
            $appointmentId,
            $appointment->doctor->name,
            $appointment->scheduled_at
        );

        return $appointment;
    }

    /**
     * Reject appointment (by doctor)
     */
    public function rejectAppointment(int $appointmentId, int $doctorId, string $reason): Appointment
    {
        $appointment = Appointment::findOrFail($appointmentId);

        if ($appointment->doctor_id !== $doctorId) {
            throw new \Exception('Hanya doctor dapat reject appointment');
        }

        if ($appointment->status !== 'pending') {
            throw new \Exception('Hanya appointment pending yang dapat di-reject');
        }

        $appointment->reject($reason);

        // Send notification ke patient
        (new NotificationService())->notifyAppointmentRejected(
            $appointment->patient_id,
            $appointmentId,
            $reason
        );

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
