<?php

namespace App\Services;

use App\Models\Konsultasi;
use App\Models\KonsultasiSummary;
use App\Models\KonsultasiMedication;
use App\Models\KonsultasiFollowUp;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Logging\Logger;

/**
 * ============================================
 * CONSULTATION SUMMARY SERVICE
 * ============================================
 * 
 * Service untuk handle consultation summary
 * (kesimpulan konsultasi).
 * 
 * @author Telemedicine App
 * @version 1.0
 */
class KonsultasiSummaryService
{
    /**
     * Create konsultasi summary
     * 
     * @param Konsultasi $konsultasi
     * @param array $data - Summary data
     * @param User $doctor - Doctor creating summary
     * @return KonsultasiSummary
     */
    public function createSummary(Konsultasi $konsultasi, array $data, User $doctor): KonsultasiSummary
    {
        // Validasi: hanya dokter konsultasi yang bisa buat summary
        if ($doctor->dokter->id !== $konsultasi->dokter_id) {
            throw new \Exception('Anda bukan dokter pada konsultasi ini');
        }

        // Create summary record
        $summary = KonsultasiSummary::create([
            'consultation_id' => $konsultasi->id,
            'doctor_id' => $doctor->id,
            'diagnosis' => $data['diagnosis'] ?? null,
            'clinical_findings' => $data['clinical_findings'] ?? null,
            'examination_results' => $data['examination_results'] ?? null,
            'treatment_plan' => $data['treatment_plan'] ?? null,
            'follow_up_date' => $data['follow_up_date'] ?? null,
            'follow_up_instructions' => $data['follow_up_instructions'] ?? null,
            'additional_notes' => $data['additional_notes'] ?? null,
            'referrals' => $data['referrals'] ?? null,
        ]);

        // Update konsultasi dengan summary info
        $konsultasi->update([
            'diagnosis' => $data['diagnosis'] ?? null,
            'findings' => $data['clinical_findings'] ?? null,
            'treatment_plan' => $data['treatment_plan'] ?? null,
            'follow_up_date' => $data['follow_up_date'] ?? null,
            'follow_up_instructions' => $data['follow_up_instructions'] ?? null,
            'summary_completed' => true,
            'summary_completed_at' => now(),
        ]);

        // Create medications jika ada
        if (!empty($data['medications'])) {
            $this->addMedications($konsultasi->id, $doctor->id, $data['medications']);
        }

        // Schedule follow-up jika ada
        if (!empty($data['follow_up_date'])) {
            $this->scheduleFollowUp($konsultasi->id, $data);
        }

        // Log action
        Logger::logTransaction('create', 'KonsultasiSummary', $summary->id, [
            'konsultasi_id' => $konsultasi->id,
            'has_medications' => !empty($data['medications']),
        ], $doctor->id);

        return $summary;
    }

    /**
     * Add medications ke konsultasi
     * 
     * @param int $konsultasiId
     * @param int $doctorId
     * @param array $medications
     */
    public function addMedications(int $konsultasiId, int $doctorId, array $medications): void
    {
        foreach ($medications as $med) {
            KonsultasiMedication::create([
                'consultation_id' => $konsultasiId,
                'doctor_id' => $doctorId,
                'medication_name' => $med['name'],
                'dose' => $med['dose'],
                'frequency' => $med['frequency'],
                'duration_days' => $med['duration_days'],
                'instructions' => $med['instructions'] ?? null,
                'route' => $med['route'] ?? 'oral',
                'status' => 'prescribed',
                'prescribed_at' => now(),
            ]);
        }
    }

    /**
     * Schedule follow-up appointment
     * 
     * @param int $konsultasiId
     * @param array $data
     */
    public function scheduleFollowUp(int $konsultasiId, array $data): void
    {
        KonsultasiFollowUp::create([
            'original_consultation_id' => $konsultasiId,
            'scheduled_date' => $data['follow_up_date'],
            'reason' => $data['follow_up_instructions'] ?? null,
            'status' => 'scheduled',
        ]);
    }

    /**
     * Get summary untuk konsultasi
     * 
     * @param int $konsultasiId
     * @return KonsultasiSummary|null
     */
    public function getSummary(int $konsultasiId): ?KonsultasiSummary
    {
        return KonsultasiSummary::where('consultation_id', $konsultasiId)
            ->with('dokter', 'medications', 'followUps')
            ->first();
    }

    /**
     * Update summary
     * 
     * @param KonsultasiSummary $summary
     * @param array $data
     * @param User $doctor
     * @return KonsultasiSummary
     */
    public function updateSummary(KonsultasiSummary $summary, array $data, User $doctor): KonsultasiSummary
    {
        // Validasi: hanya dokter yang buat summary yang bisa edit
        if ($summary->doctor_id !== $doctor->id && !$doctor->isAdmin()) {
            throw new \Exception('Anda tidak berhak edit summary ini');
        }

        // Update summary fields
        $summary->update([
            'diagnosis' => $data['diagnosis'] ?? $summary->diagnosis,
            'clinical_findings' => $data['clinical_findings'] ?? $summary->clinical_findings,
            'examination_results' => $data['examination_results'] ?? $summary->examination_results,
            'treatment_plan' => $data['treatment_plan'] ?? $summary->treatment_plan,
            'follow_up_date' => $data['follow_up_date'] ?? $summary->follow_up_date,
            'follow_up_instructions' => $data['follow_up_instructions'] ?? $summary->follow_up_instructions,
            'additional_notes' => $data['additional_notes'] ?? $summary->additional_notes,
        ]);

        // Update konsultasi
        $summary->konsultasi()->update([
            'diagnosis' => $data['diagnosis'] ?? $summary->konsultasi->diagnosis,
        ]);

        // Log action
        Logger::logTransaction('update', 'KonsultasiSummary', $summary->id, [
            'konsultasi_id' => $summary->consultation_id,
        ], $doctor->id);

        return $summary;
    }

    /**
     * Mark summary sebagai acknowledged oleh pasien
     * 
     * @param KonsultasiSummary $summary
     * @return KonsultasiSummary
     */
    public function markPatientAcknowledged(KonsultasiSummary $summary): KonsultasiSummary
    {
        $summary->update([
            'patient_acknowledged' => true,
            'patient_acknowledged_at' => now(),
        ]);

        return $summary;
    }

    /**
     * Get all summaries untuk patient
     * 
     * @param int $patientId
     * @param array $filters
     * @return array
     */
    public function getPatientSummaries(int $patientId, array $filters = []): array
    {
        $query = KonsultasiSummary::whereHas('konsultasi', function ($q) use ($patientId) {
            $q->where('pasien_id', $patientId);
        })
        ->with('konsultasi', 'dokter', 'medications')
        ->orderByDesc('created_at');

        if ($filters['acknowledged'] === true) {
            $query->where('patient_acknowledged', true);
        }

        if ($filters['from_date'] ?? null) {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if ($filters['to_date'] ?? null) {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        $per_page = $filters['per_page'] ?? 15;
        
        return [
            'summaries' => $query->paginate($per_page),
            'total' => $query->count(),
        ];
    }

    /**
     * Get all summaries untuk doctor
     * 
     * @param int $doctorId
     * @param array $filters
     * @return array
     */
    public function getDoctorSummaries(int $doctorId, array $filters = []): array
    {
        $query = KonsultasiSummary::where('doctor_id', $doctorId)
            ->with('konsultasi.pasien', 'medications')
            ->orderByDesc('created_at');

        if ($filters['acknowledged'] ?? null === false) {
            $query->where('patient_acknowledged', false);
        }

        if ($filters['from_date'] ?? null) {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        $per_page = $filters['per_page'] ?? 15;

        return [
            'summaries' => $query->paginate($per_page),
            'total' => $query->count(),
        ];
    }

    /**
     * Delete summary (admin only)
     * 
     * @param KonsultasiSummary $summary
     * @param User $user
     */
    public function deleteSummary(KonsultasiSummary $summary, User $user): void
    {
        if (!$user->isAdmin()) {
            throw new \Exception('Hanya admin yang bisa delete summary');
        }

        // Log action
        Logger::logTransaction('delete', 'KonsultasiSummary', $summary->id, [], $user->id);

        $summary->delete();
    }

    /**
     * Get summary statistics
     * 
     * @param int $doctorId
     * @return array
     */
    public function getStatistics(int $doctorId): array
    {
        return [
            'total_summaries' => KonsultasiSummary::where('doctor_id', $doctorId)->count(),
            'acknowledged' => KonsultasiSummary::where('doctor_id', $doctorId)
                ->where('patient_acknowledged', true)->count(),
            'pending_acknowledgement' => KonsultasiSummary::where('doctor_id', $doctorId)
                ->where('patient_acknowledged', false)->count(),
            'with_follow_ups' => KonsultasiSummary::where('doctor_id', $doctorId)
                ->where('follow_up_date', '!=', null)->count(),
        ];
    }
}
