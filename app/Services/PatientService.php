<?php

namespace App\Services;

use App\Models\Pasien;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Patient Service
 * 
 * Handle semua logika bisnis untuk manajemen pasien
 * Mencakup: profile management, health tracking, engagement
 */
class PatientService
{
    /**
     * Get semua pasien dengan filter dan pagination
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAllPatients(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Pasien::with('user');

        // Search by name or email
        if (isset($filters['search'])) {
            $query->whereHas('user', function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('email', 'like', '%' . $filters['search'] . '%');
            });
        }

        // Filter by status
        if (isset($filters['status'])) {
            $query->whereHas('user', function ($q) use ($filters) {
                $q->where('is_active', $filters['status'] === 'active');
            });
        }

        // Filter by date range
        if (isset($filters['from_date']) && isset($filters['to_date'])) {
            $query->whereBetween('created_at', [$filters['from_date'], $filters['to_date']]);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Get patient by ID with all relations
     *
     * @param int $id
     * @return Pasien|null
     */
    public function getPatientById(int $id): ?Pasien
    {
        return Pasien::with(['user', 'konsultasi', 'medicalRecords'])
            ->withCount(['konsultasi', 'medicalRecords'])
            ->find($id);
    }

    /**
     * Get patient by user ID
     *
     * @param int $user_id
     * @return Pasien|null
     */
    public function getPatientByUserId(int $user_id): ?Pasien
    {
        return Pasien::where('user_id', $user_id)
            ->with(['user', 'konsultasi', 'medicalRecords'])
            ->withCount(['konsultasi', 'medicalRecords'])
            ->first();
    }

    /**
     * Create patient
     *
     * @param User $user
     * @param array $data
     * @return Pasien
     */
    public function createPatient(User $user, array $data): Pasien
    {
        $patient = Pasien::create([
            'user_id' => $user->id,
            'alamat' => $data['alamat'] ?? null,
            'no_telepon' => $data['no_telepon'] ?? null,
            'tanggal_lahir' => $data['tanggal_lahir'] ?? null,
            'riwayat_penyakit' => $data['riwayat_penyakit'] ?? null,
            'alergi' => $data['alergi'] ?? null,
        ]);

        return $patient->fresh();
    }

    /**
     * Update patient
     *
     * @param Pasien $patient
     * @param array $data
     * @return Pasien
     */
    public function updatePatient(Pasien $patient, array $data): Pasien
    {
        $updateData = [];

        if (isset($data['alamat'])) {
            $updateData['alamat'] = $data['alamat'];
        }

        if (isset($data['no_telepon'])) {
            $updateData['no_telepon'] = $data['no_telepon'];
        }

        if (isset($data['tanggal_lahir'])) {
            $updateData['tanggal_lahir'] = $data['tanggal_lahir'];
        }

        if (isset($data['riwayat_penyakit'])) {
            $updateData['riwayat_penyakit'] = $data['riwayat_penyakit'];
        }

        if (isset($data['alergi'])) {
            $updateData['alergi'] = $data['alergi'];
        }

        // New fields for health tracking
        if (isset($data['emergency_contact_name'])) {
            $updateData['emergency_contact_name'] = $data['emergency_contact_name'];
        }

        if (isset($data['emergency_contact_phone'])) {
            $updateData['emergency_contact_phone'] = $data['emergency_contact_phone'];
        }

        if (isset($data['insurance_provider'])) {
            $updateData['insurance_provider'] = $data['insurance_provider'];
        }

        if (isset($data['insurance_number'])) {
            $updateData['insurance_number'] = $data['insurance_number'];
        }

        $patient->update($updateData);

        return $patient->fresh();
    }

    /**
     * Get patient profile completion percentage
     *
     * @param int $patient_id
     * @return float
     */
    public function getProfileCompletion(int $patient_id): float
    {
        $patient = Pasien::find($patient_id);

        if (!$patient) {
            return 0;
        }

        $fields = [
            'alamat' => !empty($patient->alamat),
            'no_telepon' => !empty($patient->no_telepon),
            'tanggal_lahir' => !empty($patient->tanggal_lahir),
            'riwayat_penyakit' => !empty($patient->riwayat_penyakit),
            'alergi' => !empty($patient->alergi),
            'emergency_contact' => !empty($patient->emergency_contact_name),
            'insurance' => !empty($patient->insurance_provider),
        ];

        $completedFields = array_filter($fields);
        $totalFields = count($fields);

        return round((count($completedFields) / $totalFields) * 100, 2);
    }

    /**
     * Get patient health summary
     *
     * @param int $patient_id
     * @return array
     */
    public function getPatientHealthSummary(int $patient_id): array
    {
        $patient = Pasien::with(['user', 'konsultasi', 'medicalRecords'])
            ->find($patient_id);

        if (!$patient) {
            return [];
        }

        $consultations = $patient->konsultasi;
        $medicalRecords = $patient->medicalRecords;

        // Collect unique conditions
        $conditions = [];
        foreach ($medicalRecords as $record) {
            if (is_array($record->diagnosis)) {
                $conditions = array_merge($conditions, $record->diagnosis);
            }
        }

        // Collect allergies
        $allergies = $patient->alergi ?? [];
        if (is_string($allergies)) {
            $allergies = [$allergies];
        }

        return [
            'patient_id' => $patient_id,
            'patient_name' => $patient->user->name,
            'date_of_birth' => $patient->tanggal_lahir,
            'age' => $patient->tanggal_lahir ? now()->diffInYears($patient->tanggal_lahir) : null,
            'total_consultations' => $consultations->count(),
            'total_medical_records' => $medicalRecords->count(),
            'conditions' => array_unique($conditions),
            'allergies' => is_array($allergies) ? $allergies : [$allergies],
            'last_consultation' => $consultations->sortByDesc('created_at')->first()?->created_at,
            'last_medical_record' => $medicalRecords->sortByDesc('created_at')->first()?->created_at,
            'profile_completion' => $this->getProfileCompletion($patient_id) . '%',
        ];
    }

    /**
     * Get patient appointment history
     *
     * @param int $patient_id
     * @param int $limit
     * @return mixed
     */
    public function getPatientAppointmentHistory(int $patient_id, int $limit = 10)
    {
        return Pasien::find($patient_id)?->konsultasi()
            ->with(['dokter.user'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get patient statistics
     *
     * @return array
     */
    public function getPatientStatistics(): array
    {
        return [
            'total_patients' => Pasien::count(),
            'active_patients' => Pasien::whereHas('user', function ($q) {
                $q->where('is_active', true);
            })->count(),
            'patients_with_medical_records' => Pasien::has('medicalRecords')->count(),
            'patients_with_consultations' => Pasien::has('konsultasi')->count(),
            'new_patients_this_month' => Pasien::whereMonth('created_at', date('m'))
                ->whereYear('created_at', date('Y'))
                ->count(),
        ];
    }

    /**
     * Get patients needing follow-up
     *
     * @param int $days
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPatientsNeedingFollowUp(int $days = 30, int $perPage = 15): LengthAwarePaginator
    {
        $cutoffDate = now()->subDays($days);

        return Pasien::with('user')
            ->whereHas('medicalRecords', function ($q) use ($cutoffDate) {
                $q->where('created_at', '<', $cutoffDate);
            })
            ->paginate($perPage);
    }

    /**
     * Delete patient
     *
     * @param Pasien $patient
     * @return bool
     */
    public function deletePatient(Pasien $patient): bool
    {
        return $patient->delete();
    }
}
