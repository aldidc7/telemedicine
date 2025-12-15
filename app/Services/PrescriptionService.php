<?php

namespace App\Services;

use App\Models\Prescription;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class PrescriptionService
{
    /**
     * Create prescription for appointment
     */
    public function createPrescription(
        int $appointmentId,
        int $doctorId,
        int $patientId,
        array $medications,
        ?string $notes = null,
        ?string $instructions = null,
        ?string $expiresAt = null
    ): Prescription {
        // Validasi appointment exists dan selesai
        $appointment = Appointment::findOrFail($appointmentId);
        if ($appointment->status !== 'completed') {
            throw new \Exception('Hanya appointment yang sudah selesai yang bisa diberi resep');
        }

        // Validasi doctor owns appointment
        if ($appointment->doctor_id !== $doctorId) {
            throw new \Exception('Hanya doctor yang menangani appointment ini yang bisa buat resep');
        }

        // Validasi medications tidak kosong
        if (empty($medications)) {
            throw new \Exception('Minimal ada satu obat dalam resep');
        }

        // Create prescription
        $prescription = Prescription::create([
            'appointment_id' => $appointmentId,
            'doctor_id' => $doctorId,
            'patient_id' => $patientId,
            'medications' => $medications,
            'notes' => $notes,
            'instructions' => $instructions,
            'expires_at' => $expiresAt,
            'issued_at' => now(),
            'status' => 'active',
        ]);

        // Send notification ke patient
        (new NotificationService())->notifyPrescriptionCreated(
            $patientId,
            $prescription->id,
            $appointment->doctor->name,
            count($medications)
        );

        return $prescription;
    }

    /**
     * Get prescription by ID with relationships
     */
    public function getPrescriptionDetail(int $prescriptionId): Prescription
    {
        return Prescription::with(['appointment', 'doctor', 'patient'])->findOrFail($prescriptionId);
    }

    /**
     * Get patient prescriptions
     */
    public function getPatientPrescriptions(
        int $patientId,
        ?string $status = null,
        int $page = 1,
        int $perPage = 15
    ) {
        $query = Prescription::forPatient($patientId)->with(['doctor', 'appointment']);

        if ($status) {
            if ($status === 'active') {
                $query->active();
            } elseif ($status === 'expired') {
                $query->expired();
            } else {
                $query->where('status', $status);
            }
        }

        return $query->orderBy('issued_at', 'desc')->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Get doctor's issued prescriptions
     */
    public function getDoctorPrescriptions(
        int $doctorId,
        ?string $status = null,
        int $page = 1,
        int $perPage = 15
    ) {
        $query = Prescription::forDoctor($doctorId)->with(['patient', 'appointment']);

        if ($status) {
            if ($status === 'active') {
                $query->active();
            } elseif ($status === 'expired') {
                $query->expired();
            } else {
                $query->where('status', $status);
            }
        }

        return $query->orderBy('issued_at', 'desc')->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Get active prescriptions for patient
     */
    public function getActivePrescriptions(int $patientId): Collection
    {
        return Prescription::forPatient($patientId)
            ->active()
            ->with(['doctor'])
            ->orderBy('issued_at', 'desc')
            ->get();
    }

    /**
     * Get unacknowledged prescriptions for patient
     */
    public function getUnacknowledgedPrescriptions(int $patientId): Collection
    {
        return Prescription::forPatient($patientId)
            ->unacknowledged()
            ->with(['doctor'])
            ->orderBy('issued_at', 'desc')
            ->get();
    }

    /**
     * Patient acknowledge prescription
     */
    public function acknowledgePrescription(int $prescriptionId, int $patientId): Prescription
    {
        $prescription = Prescription::findOrFail($prescriptionId);

        if ($prescription->patient_id !== $patientId) {
            throw new \Exception('Anda tidak berhak acknowledge resep ini');
        }

        $prescription->acknowledge();

        // Send notification ke doctor
        (new NotificationService())->notifyPrescriptionAcknowledged(
            $prescription->doctor_id,
            $prescriptionId,
            $prescription->patient->name
        );

        return $prescription;
    }

    /**
     * Update prescription (doctor only)
     */
    public function updatePrescription(
        int $prescriptionId,
        int $doctorId,
        array $medications,
        ?string $notes = null,
        ?string $instructions = null
    ): Prescription {
        $prescription = Prescription::findOrFail($prescriptionId);

        if ($prescription->doctor_id !== $doctorId) {
            throw new \Exception('Hanya doctor yang buat resep ini yang bisa update');
        }

        if (!empty($medications)) {
            $prescription->medications = $medications;
        }

        if ($notes !== null) {
            $prescription->notes = $notes;
        }

        if ($instructions !== null) {
            $prescription->instructions = $instructions;
        }

        $prescription->save();

        // Send notification ke patient
        (new NotificationService())->notifyPrescriptionUpdated(
            $prescription->patient_id,
            $prescriptionId,
            $prescription->doctor->name
        );

        return $prescription;
    }

    /**
     * Mark prescription as completed
     */
    public function completePrescription(int $prescriptionId, int $patientId): Prescription
    {
        $prescription = Prescription::findOrFail($prescriptionId);

        if ($prescription->patient_id !== $patientId) {
            throw new \Exception('Anda tidak berhak mark resep ini');
        }

        $prescription->markCompleted();

        return $prescription;
    }

    /**
     * Delete prescription (doctor only)
     */
    public function deletePrescription(int $prescriptionId, int $doctorId): void
    {
        $prescription = Prescription::findOrFail($prescriptionId);

        if ($prescription->doctor_id !== $doctorId) {
            throw new \Exception('Hanya doctor yang buat resep ini yang bisa delete');
        }

        if ($prescription->patient_acknowledged) {
            throw new \Exception('Tidak bisa hapus resep yang sudah di-acknowledge pasien');
        }

        $prescription->delete();
    }

    /**
     * Search prescriptions
     */
    public function searchPrescriptions(
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
            $query = Prescription::where('patient_id', $userId)->with(['doctor']);
        } else {
            $query = Prescription::where('doctor_id', $userId)->with(['patient']);
        }

        if ($search) {
            $query->where(function ($q) use ($search, $role) {
                if ($role === 'pasien') {
                    $q->whereHas('doctor', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })->orWhere('notes', 'like', "%{$search}%");
                } else {
                    $q->whereHas('patient', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })->orWhere('notes', 'like', "%{$search}%");
                }
            });
        }

        if ($status) {
            if ($status === 'active') {
                $query->active();
            } elseif ($status === 'expired') {
                $query->expired();
            } else {
                $query->where('status', $status);
            }
        }

        if ($dateFrom) {
            $query->where('issued_at', '>=', \Carbon\Carbon::parse($dateFrom)->startOfDay());
        }

        if ($dateTo) {
            $query->where('issued_at', '<=', \Carbon\Carbon::parse($dateTo)->endOfDay());
        }

        return $query->orderBy('issued_at', 'desc')->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Get prescription statistics
     */
    public function getPrescriptionStats(int $userId, string $role): array
    {
        if ($role === 'pasien') {
            $query = Prescription::where('patient_id', $userId);
        } else {
            $query = Prescription::where('doctor_id', $userId);
        }

        return [
            'total' => $query->count(),
            'active' => $query->clone()->active()->count(),
            'expired' => $query->clone()->expired()->count(),
            'completed' => $query->clone()->where('status', 'completed')->count(),
            'unacknowledged' => $query->clone()->unacknowledged()->count(),
            'total_medications' => $this->getTotalMedicationsCount($userId, $role),
        ];
    }

    /**
     * Get total medications count
     */
    private function getTotalMedicationsCount(int $userId, string $role): int
    {
        if ($role === 'pasien') {
            $prescriptions = Prescription::where('patient_id', $userId)->get();
        } else {
            $prescriptions = Prescription::where('doctor_id', $userId)->get();
        }

        $total = 0;
        foreach ($prescriptions as $prescription) {
            $total += $prescription->getMedicationCount();
        }
        return $total;
    }

    /**
     * Get prescriptions for appointment
     */
    public function getAppointmentPrescriptions(int $appointmentId): Collection
    {
        return Prescription::byAppointment($appointmentId)
            ->with(['doctor', 'patient'])
            ->get();
    }

    /**
     * Check if appointment has prescription
     */
    public function appointmentHasPrescription(int $appointmentId): bool
    {
        return Prescription::byAppointment($appointmentId)->exists();
    }

    /**
     * Get recently issued prescriptions for dashboard
     */
    public function getRecentPrescriptions(int $doctorId, int $limit = 10): Collection
    {
        return Prescription::forDoctor($doctorId)
            ->with(['patient'])
            ->orderBy('issued_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
