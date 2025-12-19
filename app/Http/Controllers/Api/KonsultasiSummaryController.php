<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Konsultasi;
use App\Models\KonsultasiSummary;
use App\Services\KonsultasiSummaryService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Logging\Logger;

/**
 * ============================================
 * KONSULTASI SUMMARY CONTROLLER
 * ============================================
 * 
 * Handle semua operasi consultation summary:
 * - Create summary (dokter akhiri konsultasi)
 * - View summary (pasien & dokter)
 * - Edit summary (dokter)
 * - Mark acknowledged (pasien)
 * - Download/Print summary
 * 
 * API Endpoints:
 * - POST /api/v1/consultations/{id}/summary
 * - GET /api/v1/consultations/{id}/summary
 * - PUT /api/v1/consultations/{id}/summary
 * - PUT /api/v1/consultations/{id}/summary/acknowledge
 * - GET /api/v1/patient/summaries
 * - GET /api/v1/doctor/summaries
 * 
 * @author Telemedicine App
 * @version 1.0
 */
class KonsultasiSummaryController extends Controller
{
    use ApiResponse;

    private $summaryService;

    public function __construct(KonsultasiSummaryService $summaryService)
    {
        $this->summaryService = $summaryService;
    }

    /**
     * ============================================
     * CREATE SUMMARY - Dokter buat kesimpulan konsultasi
     * ============================================
     * 
     * POST /api/v1/consultations/{id}/summary
     * 
     * Body:
     * {
     *   "diagnosis": "Demam berdarah",
     *   "clinical_findings": "Ruam petekia, demam 39°C",
     *   "examination_results": "Trombosit 90.000",
     *   "treatment_plan": "Istirahat, minum banyak, monitor kondisi",
     *   "follow_up_date": "2025-12-26",
     *   "follow_up_instructions": "Kembali jika demam tidak turun",
     *   "medications": [
     *     {
     *       "name": "Paracetamol",
     *       "dose": "500mg",
     *       "frequency": "3x sehari",
     *       "duration_days": 5,
     *       "instructions": "Setelah makan"
     *     }
     *   ],
     *   "referrals": ["Spesialis penyakit dalam jika kondisi memburuk"],
     *   "additional_notes": "Catatan tambahan..."
     * }
     * 
     * @param int $id - Consultation ID
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store($id, Request $request)
    {
        $konsultasi = Konsultasi::find($id);
        if (!$konsultasi) {
            return $this->notFoundResponse('Konsultasi tidak ditemukan');
        }

        $user = Auth::user();

        // Validasi: hanya dokter konsultasi yang bisa buat summary
        if (!$user->isDokter() || $user->dokter->id !== $konsultasi->dokter_id) {
            if (!$user->isAdmin()) {
                return $this->forbiddenResponse('Hanya dokter konsultasi yang bisa membuat summary');
            }
        }

        // Validasi data
        $validated = $request->validate([
            'diagnosis' => 'required|string|max:1000',
            'clinical_findings' => 'nullable|string',
            'examination_results' => 'nullable|string',
            'treatment_plan' => 'required|string',
            'follow_up_date' => 'nullable|date|after:today',
            'follow_up_instructions' => 'nullable|string',
            'medications' => 'nullable|array',
            'medications.*.name' => 'required_with:medications|string',
            'medications.*.dose' => 'required_with:medications|string',
            'medications.*.frequency' => 'required_with:medications|string',
            'medications.*.duration_days' => 'required_with:medications|integer',
            'medications.*.instructions' => 'nullable|string',
            'medications.*.route' => 'nullable|string|in:oral,injection,topical,inhalation',
            'referrals' => 'nullable|array',
            'additional_notes' => 'nullable|string',
        ]);

        try {
            // Create summary
            $summary = $this->summaryService->createSummary($konsultasi, $validated, $user);

            // Log action
            Logger::logApiRequest('POST', 'consultations/{id}/summary', [
                'consultation_id' => $konsultasi->id,
                'has_medications' => !empty($validated['medications']),
            ], $user->id);

            return $this->createdResponse($summary, 'Kesimpulan konsultasi berhasil dibuat', [
                'medications_count' => count($validated['medications'] ?? []),
                'has_follow_up' => !empty($validated['follow_up_date']),
            ]);
        } catch (\Exception $e) {
            Logger::logError($e, 'KonsultasiSummaryController@store', [
                'consultation_id' => $konsultasi->id,
            ]);

            return $this->badRequestResponse('Gagal membuat summary: ' . $e->getMessage());
        }
    }

    /**
     * ============================================
     * GET SUMMARY - Ambil kesimpulan konsultasi
     * ============================================
     * 
     * GET /api/v1/consultations/{id}/summary
     * 
     * @param int $id - Consultation ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $konsultasi = Konsultasi::with('pasien', 'dokter')->find($id);
        if (!$konsultasi) {
            return $this->notFoundResponse('Konsultasi tidak ditemukan');
        }

        $user = Auth::user();

        // Check authorization
        $isAuthorized = false;
        if ($user->isPasien() && $user->pasien->id === $konsultasi->pasien_id) {
            $isAuthorized = true;
        } elseif ($user->isDokter() && $user->dokter->id === $konsultasi->dokter_id) {
            $isAuthorized = true;
        } elseif ($user->isAdmin()) {
            $isAuthorized = true;
        }

        if (!$isAuthorized) {
            return $this->forbiddenResponse('Anda tidak berhak mengakses summary ini');
        }

        // Get summary
        $summary = $this->summaryService->getSummary($konsultasi->id);
        if (!$summary) {
            return $this->notFoundResponse('Summary untuk konsultasi ini belum ada');
        }

        // Mark as acknowledged jika pasien
        if ($user->isPasien() && !$summary->patient_acknowledged) {
            $this->summaryService->markPatientAcknowledged($summary);
        }

        return $this->successResponse($summary, 'Summary konsultasi berhasil diambil', [
            'medications_count' => $summary->medications()->count(),
            'has_follow_up' => !empty($summary->follow_up_date),
        ]);
    }

    /**
     * ============================================
     * UPDATE SUMMARY - Edit kesimpulan konsultasi
     * ============================================
     * 
     * PUT /api/v1/consultations/{id}/summary
     * 
     * @param int $id - Consultation ID
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id, Request $request)
    {
        $konsultasi = Konsultasi::find($id);
        if (!$konsultasi) {
            return $this->notFoundResponse('Konsultasi tidak ditemukan');
        }

        $user = Auth::user();

        // Get summary
        $summary = $this->summaryService->getSummary($konsultasi->id);
        if (!$summary) {
            return $this->notFoundResponse('Summary tidak ditemukan');
        }

        // Validasi: hanya dokter atau admin
        if (!$user->isDokter() && !$user->isAdmin()) {
            return $this->forbiddenResponse('Hanya dokter yang bisa edit summary');
        }

        if (!$user->isAdmin() && $user->dokter->id !== $summary->doctor_id) {
            return $this->forbiddenResponse('Hanya dokter yang membuat summary yang bisa edit');
        }

        $validated = $request->validate([
            'diagnosis' => 'sometimes|string',
            'clinical_findings' => 'sometimes|string',
            'examination_results' => 'sometimes|string',
            'treatment_plan' => 'sometimes|string',
            'follow_up_date' => 'sometimes|nullable|date',
            'follow_up_instructions' => 'sometimes|nullable|string',
            'additional_notes' => 'sometimes|nullable|string',
        ]);

        try {
            $updated = $this->summaryService->updateSummary($summary, $validated, $user);

            return $this->successResponse($updated, 'Summary berhasil diperbarui');
        } catch (\Exception $e) {
            return $this->badRequestResponse('Gagal update summary: ' . $e->getMessage());
        }
    }

    /**
     * ============================================
     * ACKNOWLEDGE SUMMARY - Pasien lihat summary
     * ============================================
     * 
     * PUT /api/v1/consultations/{id}/summary/acknowledge
     * 
     * @param int $id - Consultation ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function acknowledge($id)
    {
        $konsultasi = Konsultasi::find($id);
        if (!$konsultasi) {
            return $this->notFoundResponse('Konsultasi tidak ditemukan');
        }

        $user = Auth::user();

        // Validasi: hanya pasien konsultasi
        if (!$user->isPasien() || $user->pasien->id !== $konsultasi->pasien_id) {
            if (!$user->isAdmin()) {
                return $this->forbiddenResponse('Hanya pasien yang bisa acknowledge summary');
            }
        }

        $summary = $this->summaryService->getSummary($konsultasi->id);
        if (!$summary) {
            return $this->notFoundResponse('Summary tidak ditemukan');
        }

        $updated = $this->summaryService->markPatientAcknowledged($summary);

        return $this->successResponse($updated, 'Summary sudah dikonfirmasi dibaca');
    }

    /**
     * ============================================
     * PATIENT SUMMARIES - Daftar summary pasien
     * ============================================
     * 
     * GET /api/v1/patient/summaries
     * 
     * Query params:
     * - per_page: 15
     * - acknowledged: true/false
     * - from_date: YYYY-MM-DD
     * - to_date: YYYY-MM-DD
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function patientSummaries(Request $request)
    {
        $user = Auth::user();

        if (!$user->isPasien()) {
            return $this->forbiddenResponse('Hanya pasien yang bisa akses endpoint ini');
        }

        $filters = [
            'per_page' => $request->get('per_page', 15),
            'acknowledged' => $request->get('acknowledged'),
            'from_date' => $request->get('from_date'),
            'to_date' => $request->get('to_date'),
        ];

        $result = $this->summaryService->getPatientSummaries($user->pasien->id, $filters);

        return $this->paginatedResponse($result['summaries'], $result['summaries']->getOptions(), 'Daftar summary pasien', [
            'total_summaries' => $result['total'],
        ]);
    }

    /**
     * ============================================
     * DOCTOR SUMMARIES - Daftar summary dokter
     * ============================================
     * 
     * GET /api/v1/doctor/summaries
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function doctorSummaries(Request $request)
    {
        $user = Auth::user();

        if (!$user->isDokter()) {
            return $this->forbiddenResponse('Hanya dokter yang bisa akses endpoint ini');
        }

        $filters = [
            'per_page' => $request->get('per_page', 15),
            'acknowledged' => $request->get('acknowledged'),
            'from_date' => $request->get('from_date'),
            'to_date' => $request->get('to_date'),
        ];

        $result = $this->summaryService->getDoctorSummaries($user->dokter->id, $filters);

        // Get statistics
        $stats = $this->summaryService->getStatistics($user->dokter->id);

        return $this->paginatedResponse($result['summaries'], $result['summaries']->getOptions(), 'Daftar summary dokter', [
            'statistics' => $stats,
        ]);
    }
}
