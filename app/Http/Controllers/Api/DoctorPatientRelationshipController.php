<?php

namespace App\Http\Controllers\Api;

use App\Models\DoctorPatientRelationship;
use App\Models\Dokter;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * ============================================
 * DOCTOR-PATIENT RELATIONSHIP CONTROLLER
 * ============================================
 * 
 * Controller untuk manage hubungan dokter-pasien.
 * Requirement: Ryan Haight Act compliance.
 * 
 * Fitur:
 * 1. Create relationship (doctor establish)
 * 2. Get relationships (doctor's patients)
 * 3. Check relationship exists
 * 4. Get patient's doctors
 * 5. Terminate relationship
 * 6. View relationship history
 * 
 * Security:
 * - Hanya dokter yang bisa create relationship untuk dirinya
 * - Hanya pasien bisa lihat dokter mereka sendiri
 * - Hanya admin bisa lihat semua relationships
 * - Semua akses di-log untuk audit
 * 
 * @author Aplikasi Telemedicine RSUD dr. R. Soedarsono
 * @version 1.0
 * @date 2025-12-20
 */
class DoctorPatientRelationshipController extends Controller
{
    /**
     * 1. CREATE RELATIONSHIP
     * Dokter establish hubungan dengan pasien (Ryan Haight Act requirement)
     * 
     * POST /api/v1/doctor-patient-relationships
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function establish(Request $request)
    {
        // Auth check - hanya dokter
        $user = $request->user();
        $doctor = Dokter::where('user_id', $user->id)->firstOrFail();
        
        // Validasi input
        $validated = $request->validate([
            'patient_id' => 'required|integer|exists:users,id',
            'establishment_method' => [
                'required',
                'in:' . implode(',', array_keys(DoctorPatientRelationship::ESTABLISHMENT_METHODS)),
            ],
            'notes' => 'nullable|string|max:1000',
        ]);
        
        // Check pasien exist dan bukan dokter
        $patient = User::findOrFail($validated['patient_id']);
        if ($patient->role !== 'patient') {
            return response()->json([
                'success' => false,
                'message' => 'User bukan pasien',
            ], 422);
        }
        
        // Check relationship belum exist
        $existing = DoctorPatientRelationship::where('doctor_id', $doctor->id)
            ->where('patient_id', $patient->id)
            ->first();
        
        if ($existing && $existing->status === 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Hubungan dokter-pasien sudah aktif',
            ], 422);
        }
        
        // Create relationship
        $relationship = DoctorPatientRelationship::create([
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'establishment_method' => $validated['establishment_method'],
            'status' => 'active',
            'established_at' => now(),
            'notes' => $validated['notes'] ?? null,
        ]);
        
        // Log activity
        $this->logActivity('established', $relationship, $user);
        
        // Activity logging (untuk audit trail)
        activity()
            ->causedBy($user)
            ->performedOn($relationship)
            ->withProperties(['method' => $validated['establishment_method']])
            ->log('doctor-patient-relationship-established');
        
        return response()->json([
            'success' => true,
            'message' => 'Hubungan dokter-pasien berhasil dibuat',
            'data' => $relationship->load('doctor', 'patient'),
        ], 201);
    }

    /**
     * 2. GET DOCTOR'S PATIENTS
     * Dokter lihat daftar pasiennya
     * 
     * GET /api/v1/doctor-patient-relationships/my-patients
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMyPatients(Request $request)
    {
        $user = $request->user();
        $doctor = Dokter::where('user_id', $user->id)->firstOrFail();
        
        $relationships = DoctorPatientRelationship::with('patient')
            ->forDoctor($doctor->id)
            ->where('status', 'active')
            ->orderBy('established_at', 'desc')
            ->paginate(15);
        
        return response()->json([
            'success' => true,
            'message' => 'Daftar pasien berhasil diambil',
            'data' => $relationships,
        ]);
    }

    /**
     * 3. CHECK RELATIONSHIP EXISTS
     * Cek apakah hubungan dokter-pasien ada
     * Requirement untuk telemedicine consultation
     * 
     * GET /api/v1/doctor-patient-relationships/check/{patientId}
     * 
     * @param int $patientId
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkRelationship($patientId, Request $request)
    {
        $user = $request->user();
        $doctor = Dokter::where('user_id', $user->id)->firstOrFail();
        
        $relationship = DoctorPatientRelationship::where('doctor_id', $doctor->id)
            ->where('patient_id', $patientId)
            ->first();
        
        $hasRelationship = $relationship && $relationship->isActive();
        
        // Log check untuk audit
        if (!$hasRelationship) {
            Log::info('Doctor checked non-existent patient relationship', [
                'doctor_id' => $doctor->id,
                'patient_id' => $patientId,
                'ip' => $request->ip(),
            ]);
        }
        
        return response()->json([
            'success' => true,
            'has_relationship' => $hasRelationship,
            'relationship' => $hasRelationship ? $relationship->load('doctor', 'patient') : null,
        ]);
    }

    /**
     * 4. GET PATIENT'S DOCTORS
     * Pasien lihat daftar dokter yang terdaftar dengannya
     * 
     * GET /api/v1/doctor-patient-relationships/my-doctors
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMyDoctors(Request $request)
    {
        $patient = $request->user();
        
        // Mark as accessed untuk audit
        $relationships = DoctorPatientRelationship::where('patient_id', $patient->id)
            ->where('status', 'active')
            ->get();
        
        foreach ($relationships as $rel) {
            $rel->markAsAccessed();
        }
        
        $doctors = $relationships->load('doctor');
        
        return response()->json([
            'success' => true,
            'message' => 'Daftar dokter berhasil diambil',
            'data' => $doctors,
        ]);
    }

    /**
     * 5. TERMINATE RELATIONSHIP
     * Akhiri hubungan dokter-pasien
     * 
     * PUT /api/v1/doctor-patient-relationships/{relationshipId}/terminate
     * 
     * @param int $relationshipId
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function terminate($relationshipId, Request $request)
    {
        $user = $request->user();
        $relationship = DoctorPatientRelationship::findOrFail($relationshipId);
        
        // Authorization: dokter yang punya relationship atau admin
        $doctor = Dokter::where('user_id', $user->id)->first();
        if ($user->role !== 'admin' && (!$doctor || $doctor->id !== $relationship->doctor_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }
        
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);
        
        // Terminate
        $relationship->terminate($validated['reason']);
        
        // Log activity
        $this->logActivity('terminated', $relationship, $user, $validated);
        
        activity()
            ->causedBy($user)
            ->performedOn($relationship)
            ->withProperties(['reason' => $validated['reason']])
            ->log('doctor-patient-relationship-terminated');
        
        return response()->json([
            'success' => true,
            'message' => 'Hubungan dokter-pasien berhasil diakhiri',
            'data' => $relationship,
        ]);
    }

    /**
     * 6. GET RELATIONSHIP HISTORY
     * Lihat history/audit dari suatu relationship
     * 
     * GET /api/v1/doctor-patient-relationships/{relationshipId}/history
     * 
     * @param int $relationshipId
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getHistory($relationshipId, Request $request)
    {
        $user = $request->user();
        $relationship = DoctorPatientRelationship::findOrFail($relationshipId);
        
        // Authorization: doctor, patient, atau admin
        $doctor = Dokter::where('user_id', $user->id)->first();
        $isAuthorized = $user->role === 'admin' ||
                       ($doctor && $doctor->id === $relationship->doctor_id) ||
                       ($user->id === $relationship->patient_id);
        
        if (!$isAuthorized) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }
        
        // Get audit logs dari activity_logs
        $audits = DB::table('activity_log')
            ->where('subject_type', 'App\Models\DoctorPatientRelationship')
            ->where('subject_id', $relationship->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        // Get relationship audit table juga
        $relationshipAudits = DB::table('doctor_patient_relationship_audit')
            ->where('relationship_id', $relationship->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return response()->json([
            'success' => true,
            'message' => 'History berhasil diambil',
            'relationship' => $relationship->load('doctor', 'patient'),
            'activity_logs' => $audits,
            'audit_logs' => $relationshipAudits,
        ]);
    }

    /**
     * HELPER: Log activity untuk audit trail
     */
    private function logActivity($action, $relationship, $user, $data = null)
    {
        DB::table('doctor_patient_relationship_audit')->insert([
            'relationship_id' => $relationship->id,
            'action' => $action,
            'user_type' => $user->role,
            'user_id' => $user->id,
            'ip_address' => request()->ip(),
            'new_data' => json_encode($data ?? $relationship->toArray()),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
