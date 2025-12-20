<?php

namespace App\Http\Controllers\Api;

use App\Models\Konsultasi;
use App\Models\AuditLog;
use App\Models\ActivityLog;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * ============================================
 * PATIENT MEDICAL DATA ACCESS CONTROLLER
 * ============================================
 * 
 * Controller untuk patient access ke medical data mereka sendiri.
 * Compliance requirement: Pasien punya hak akses penuh ke medical records mereka.
 * 
 * Fitur:
 * 1. Get medical records history
 * 2. Get consultation details
 * 3. Export medical records (PDF)
 * 4. Get prescription history
 * 5. View billing/payment records
 * 
 * Security:
 * - Hanya patient bisa akses data mereka sendiri
 * - Semua akses di-log untuk audit
 * - Data dienkripsi di database
 * - Export requests logged
 * 
 * @author Aplikasi Telemedicine RSUD dr. R. Soedarsono
 * @version 1.0
 * @date 2025-12-20
 */
class PatientMedicalDataController extends Controller
{
    /**
     * 1. GET MEDICAL RECORDS HISTORY
     * Patient lihat semua medical records mereka
     * 
     * GET /api/v1/patient/medical-records
     * Query params: page, per_page, date_from, date_to
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMedicalRecords(Request $request)
    {
        $patient = $request->user();
        
        // Validate query params
        $validated = $request->validate([
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:5|max:100',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
        ]);
        
        // Get consultations (medical records)
        $query = Konsultasi::where('patient_id', $patient->id)
            ->with('doctor')
            ->orderBy('created_at', 'desc');
        
        // Filter by date range
        if ($validated['date_from'] ?? null) {
            $query->whereDate('created_at', '>=', $validated['date_from']);
        }
        if ($validated['date_to'] ?? null) {
            $query->whereDate('created_at', '<=', $validated['date_to']);
        }
        
        $records = $query->paginate($validated['per_page'] ?? 15);
        
        // Log access untuk audit
        $this->logDataAccess('medical_records_view', $patient);
        
        return response()->json([
            'success' => true,
            'message' => 'Medical records retrieved successfully',
            'data' => $records,
        ]);
    }

    /**
     * 2. GET CONSULTATION DETAILS
     * Patient lihat detail konsultasi tertentu
     * Includes: diagnosis, prescription, notes, attachments
     * 
     * GET /api/v1/patient/medical-records/{id}
     * 
     * @param int $consultationId
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getConsultationDetails($consultationId, Request $request)
    {
        $patient = $request->user();
        
        $consultation = Konsultasi::findOrFail($consultationId);
        
        // Authorization: hanya patient punya akses ke data mereka
        if ($consultation->patient_id !== $patient->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to medical record',
            ], 403);
        }
        
        // Load all related data
        $consultation->load([
            'doctor',
            'dokter',
            'messages',
            'summary',
            'prescriptions',
            'attachments',
        ]);
        
        // Log access
        $this->logDataAccess('consultation_details_view', $patient, $consultation->id);
        
        return response()->json([
            'success' => true,
            'message' => 'Consultation details retrieved',
            'data' => $consultation,
        ]);
    }

    /**
     * 3. GET PRESCRIPTION HISTORY
     * Patient lihat semua resep yang pernah diberikan
     * 
     * GET /api/v1/patient/prescriptions
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPrescriptionHistory(Request $request)
    {
        $patient = $request->user();
        
        $validated = $request->validate([
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:5|max:100',
        ]);
        
        // Get prescriptions through consultations
        $prescriptions = DB::table('prescriptions')
            ->join('consultations', 'prescriptions.consultation_id', '=', 'consultations.id')
            ->where('consultations.patient_id', $patient->id)
            ->select([
                'prescriptions.*',
                'consultations.doctor_id',
                'consultations.created_at as consultation_date',
            ])
            ->orderBy('prescriptions.created_at', 'desc')
            ->paginate($validated['per_page'] ?? 15);
        
        // Log access
        $this->logDataAccess('prescriptions_view', $patient);
        
        return response()->json([
            'success' => true,
            'message' => 'Prescription history retrieved',
            'data' => $prescriptions,
        ]);
    }

    /**
     * 4. GET CONSULTATION SUMMARY
     * Patient lihat ringkasan konsultasi
     * Includes: diagnosis, treatment plan, follow-up
     * 
     * GET /api/v1/patient/medical-records/{id}/summary
     * 
     * @param int $consultationId
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getConsultationSummary($consultationId, Request $request)
    {
        $patient = $request->user();
        
        $consultation = Konsultasi::with('summary')
            ->findOrFail($consultationId);
        
        // Check authorization
        if ($consultation->patient_id !== $patient->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }
        
        // Log access
        $this->logDataAccess('consultation_summary_view', $patient, $consultation->id);
        
        return response()->json([
            'success' => true,
            'message' => 'Consultation summary retrieved',
            'data' => $consultation->summary,
        ]);
    }

    /**
     * 5. GET DATA ACCESS HISTORY
     * Patient lihat siapa saja yang pernah akses data mereka
     * GDPR & Privacy compliance requirement
     * 
     * GET /api/v1/patient/data-access-history
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDataAccessHistory(Request $request)
    {
        $patient = $request->user();
        
        // Get all access logs untuk user ini
        $accessLogs = DB::table('patient_data_access_log')
            ->where('patient_id', $patient->id)
            ->orderBy('accessed_at', 'desc')
            ->paginate(20);
        
        return response()->json([
            'success' => true,
            'message' => 'Data access history retrieved',
            'data' => $accessLogs,
        ]);
    }

    /**
     * 6. EXPORT MEDICAL RECORDS
     * Patient download medical records as PDF
     * 
     * GET /api/v1/patient/medical-records/export/pdf
     * Query: consultation_id (optional for single record)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function exportRecords(Request $request)
    {
        $patient = $request->user();
        
        $validated = $request->validate([
            'consultation_id' => 'nullable|integer|exists:consultations,id',
            'format' => 'nullable|in:pdf,csv,json',
        ]);
        
        $format = $validated['format'] ?? 'pdf';
        
        // Get data to export
        if ($validated['consultation_id'] ?? null) {
            // Single consultation
            $consultation = Konsultasi::findOrFail($validated['consultation_id']);
            if ($consultation->patient_id !== $patient->id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
            $data = $consultation->load('doctor', 'summary', 'prescriptions');
        } else {
            // All consultations
            $data = Konsultasi::where('patient_id', $patient->id)
                ->with('doctor', 'summary', 'prescriptions')
                ->get();
        }
        
        // Log export
        $this->logDataAccess('medical_records_export_' . $format, $patient);
        
        // In real app, generate PDF/CSV
        // For now, return JSON
        return response()->json([
            'success' => true,
            'message' => 'Medical records ready for export',
            'format' => $format,
            'data' => $data,
        ]);
    }

    /**
     * 7. REQUEST DATA DELETION
     * Patient dapat request untuk delete data mereka (Right to be Forgotten - GDPR)
     * 
     * POST /api/v1/patient/request-data-deletion
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function requestDataDeletion(Request $request)
    {
        $patient = $request->user();
        
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
            'confirm' => 'required|boolean|accepted',
        ]);
        
        // Create deletion request
        $deletionRequest = DB::table('patient_deletion_requests')->insert([
            'patient_id' => $patient->id,
            'reason' => $validated['reason'],
            'status' => 'pending',
            'requested_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Log
        $this->logDataAccess('deletion_request_created', $patient);
        
        return response()->json([
            'success' => true,
            'message' => 'Data deletion request submitted. Admin will review within 30 days.',
        ], 201);
    }

    /**
     * Helper: Log all data access untuk audit trail
     */
    private function logDataAccess($action, $patient, $targetId = null)
    {
        DB::table('patient_data_access_log')->insert([
            'patient_id' => $patient->id,
            'action' => $action,
            'target_id' => $targetId,
            'ip_address' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
            'accessed_at' => now(),
            'created_at' => now(),
        ]);
        
        // Also log to activity log
        activity()
            ->causedBy($patient)
            ->withProperties(['action' => $action, 'target_id' => $targetId])
            ->log('patient_data_access_' . $action);
    }
}
