<?php

namespace App\Services;

use App\Models\MedicalRecord;
use App\Models\Pasien;
use App\Models\Dokter;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Medical Record Service
 * 
 * Handle semua logika bisnis untuk rekam medis
 * Mencakup: CRUD, validasi, dan analisis data kesehatan
 */
class MedicalRecordService
{
    /**
     * Get semua medical records dengan filter dan pagination
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAllMedicalRecords(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = MedicalRecord::with(['pasien.user', 'dokter.user', 'konsultasi']);

        // Filter by patient
        if (isset($filters['patient_id'])) {
            $query->where('patient_id', $filters['patient_id']);
        }

        // Filter by doctor
        if (isset($filters['doctor_id'])) {
            $query->where('doctor_id', $filters['doctor_id']);
        }

        // Filter by consultation
        if (isset($filters['consultation_id'])) {
            $query->where('consultation_id', $filters['consultation_id']);
        }

        // Search by diagnosis
        if (isset($filters['search'])) {
            $query->where('diagnosis', 'like', '%' . $filters['search'] . '%');
        }

        // Filter by date range
        if (isset($filters['from_date']) && isset($filters['to_date'])) {
            $query->whereBetween('created_at', [$filters['from_date'], $filters['to_date']]);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Get medical record by ID with all relations
     *
     * @param int $id
     * @return MedicalRecord|null
     */
    public function getMedicalRecordById(int $id): ?MedicalRecord
    {
        return MedicalRecord::with(['pasien.user', 'dokter.user', 'konsultasi'])
            ->find($id);
    }

    /**
     * Get patient medical records
     *
     * @param int $patient_id
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPatientMedicalRecords(int $patient_id, int $perPage = 15): LengthAwarePaginator
    {
        return MedicalRecord::where('patient_id', $patient_id)
            ->with(['dokter.user', 'konsultasi'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get doctor's created medical records
     *
     * @param int $doctor_id
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getDoctorMedicalRecords(int $doctor_id, int $perPage = 15): LengthAwarePaginator
    {
        return MedicalRecord::where('doctor_id', $doctor_id)
            ->with(['pasien.user', 'konsultasi'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Create medical record
     *
     * @param array $data
     * @return MedicalRecord
     */
    public function createMedicalRecord(array $data): MedicalRecord
    {
        $record = MedicalRecord::create([
            'patient_id' => $data['patient_id'],
            'doctor_id' => $data['doctor_id'],
            'consultation_id' => $data['consultation_id'] ?? null,
            'diagnosis' => $data['diagnosis'] ?? [],
            'symptoms' => $data['symptoms'] ?? [],
            'treatment' => $data['treatment'] ?? [],
            'prescriptions' => $data['prescriptions'] ?? [],
            'notes' => $data['notes'] ?? null,
        ]);

        return $record->fresh();
    }

    /**
     * Update medical record
     *
     * @param MedicalRecord $record
     * @param array $data
     * @return MedicalRecord
     */
    public function updateMedicalRecord(MedicalRecord $record, array $data): MedicalRecord
    {
        $updateData = [];

        if (isset($data['diagnosis'])) {
            $updateData['diagnosis'] = $data['diagnosis'];
        }

        if (isset($data['symptoms'])) {
            $updateData['symptoms'] = $data['symptoms'];
        }

        if (isset($data['treatment'])) {
            $updateData['treatment'] = $data['treatment'];
        }

        if (isset($data['prescriptions'])) {
            $updateData['prescriptions'] = $data['prescriptions'];
        }

        if (isset($data['notes'])) {
            $updateData['notes'] = $data['notes'];
        }

        $record->update($updateData);

        return $record->fresh();
    }

    /**
     * Get patient medical history summary
     *
     * @param int $patient_id
     * @return array
     */
    public function getPatientMedicalHistory(int $patient_id): array
    {
        $records = MedicalRecord::where('patient_id', $patient_id)
            ->orderBy('created_at', 'desc')
            ->get();

        $allDiagnoses = [];
        $allSymptoms = [];
        $allPrescriptions = [];

        foreach ($records as $record) {
            if (is_array($record->diagnosis)) {
                $allDiagnoses = array_merge($allDiagnoses, $record->diagnosis);
            }
            if (is_array($record->symptoms)) {
                $allSymptoms = array_merge($allSymptoms, $record->symptoms);
            }
            if (is_array($record->prescriptions)) {
                $allPrescriptions = array_merge($allPrescriptions, $record->prescriptions);
            }
        }

        return [
            'total_records' => $records->count(),
            'last_visit' => $records->first()?->created_at,
            'diagnoses' => array_unique($allDiagnoses),
            'symptoms_history' => array_unique($allSymptoms),
            'current_prescriptions' => array_unique($allPrescriptions),
        ];
    }

    /**
     * Get patient statistics
     *
     * @param int $patient_id
     * @return array
     */
    public function getPatientStatistics(int $patient_id): array
    {
        $records = MedicalRecord::where('patient_id', $patient_id)->count();
        $lastRecord = MedicalRecord::where('patient_id', $patient_id)
            ->latest('created_at')
            ->first();

        return [
            'total_medical_records' => $records,
            'last_record_date' => $lastRecord?->created_at,
            'last_diagnosis' => $lastRecord?->diagnosis ? reset($lastRecord->diagnosis) : null,
        ];
    }

    /**
     * Check for allergy alerts
     *
     * @param int $patient_id
     * @param string $medication
     * @return array
     */
    public function checkAllergyAlerts(int $patient_id, string $medication): array
    {
        $alerts = [];
        
        // Get patient medical records
        $records = MedicalRecord::where('patient_id', $patient_id)->get();

        foreach ($records as $record) {
            if (is_array($record->symptoms)) {
                // Check for allergy-related symptoms
                $allergySymptoms = array_filter($record->symptoms, function($symptom) {
                    return stripos($symptom, 'allergy') !== false || 
                           stripos($symptom, 'alergi') !== false;
                });

                if (!empty($allergySymptoms)) {
                    $alerts[] = [
                        'type' => 'allergy_history',
                        'message' => 'Pasien memiliki riwayat alergi',
                        'symptoms' => $allergySymptoms,
                        'date' => $record->created_at,
                    ];
                }
            }
        }

        return $alerts;
    }

    /**
     * Get medical records statistics
     *
     * @return array
     */
    public function getMedicalRecordsStats(): array
    {
        return [
            'total_records' => MedicalRecord::count(),
            'total_patients_with_records' => MedicalRecord::distinct('patient_id')->count(),
            'total_doctors_with_records' => MedicalRecord::distinct('doctor_id')->count(),
            'records_this_month' => MedicalRecord::whereMonth('created_at', date('m'))
                ->whereYear('created_at', date('Y'))
                ->count(),
        ];
    }

    /**
     * Export medical record to array (for PDF/export)
     *
     * @param int $id
     * @return array|null
     */
    public function exportMedicalRecord(int $id): ?array
    {
        $record = $this->getMedicalRecordById($id);

        if (!$record) {
            return null;
        }

        return [
            'mrn' => $record->medical_record_number,
            'patient' => [
                'name' => $record->pasien->user->name,
                'date_of_birth' => $record->pasien->tanggal_lahir,
            ],
            'doctor' => [
                'name' => $record->dokter->user->name,
                'specialization' => $record->dokter->specialization,
            ],
            'diagnosis' => $record->diagnosis,
            'symptoms' => $record->symptoms,
            'treatment' => $record->treatment,
            'prescriptions' => $record->prescriptions,
            'notes' => $record->notes,
            'created_at' => $record->created_at,
            'updated_at' => $record->updated_at,
        ];
    }
}
