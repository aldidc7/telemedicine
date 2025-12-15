<?php

namespace App\Http\Controllers\Api;

use App\Models\Dokter;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Doctor Verification Controller
 * 
 * Handle verifikasi dokter oleh admin
 * - GET /api/v1/admin/doctors/pending - List dokter pending verifikasi
 * - POST /api/v1/admin/doctors/{id}/approve - Approve dokter
 * - POST /api/v1/admin/doctors/{id}/reject - Reject dokter
 */
class DoctorVerificationController extends BaseController
{
    /**
     * List dokter yang pending verifikasi (untuk admin)
     */
    public function pendingDoctors()
    {
        $doctors = Dokter::pending()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->successResponse($doctors, 'Data dokter pending');
    }

    /**
     * Approve dokter (verifikasi dokter)
     */
    public function approvDoctor(Request $request, $id)
    {
        $this->authorize('update', Dokter::class); // Admin only

        $request->validate([
            'notes' => 'nullable|string|max:500'
        ]);

        $result = DB::transaction(function () use ($id, $request) {
            $doctor = Dokter::findOrFail($id);

            $doctor->update([
                'is_verified' => true,
                'verified_at' => now(),
                'verified_by_admin_id' => auth()->id(),
                'verification_notes' => $request->notes ?? null,
            ]);

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'approve_doctor',
                'description' => "Admin approved doctor: {$doctor->user->name}",
                'data' => [
                    'doctor_id' => $doctor->id,
                    'doctor_name' => $doctor->user->name,
                    'notes' => $request->notes,
                ]
            ]);

            return $doctor;
        });

        return $this->successResponse($result, 'Dokter berhasil diverifikasi');
    }

    /**
     * Reject dokter
     */
    public function rejectDoctor(Request $request, $id)
    {
        $this->authorize('update', Dokter::class); // Admin only

        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        $result = DB::transaction(function () use ($id, $request) {
            $doctor = Dokter::findOrFail($id);
            $doctorName = $doctor->user->name;

            // Log activity sebelum delete
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'reject_doctor',
                'description' => "Admin rejected doctor: {$doctorName}",
                'data' => [
                    'doctor_id' => $doctor->id,
                    'doctor_name' => $doctorName,
                    'reason' => $request->reason,
                ]
            ]);

            // Delete dokter dan user-nya
            $doctor->user->delete();
            $doctor->delete();

            return [
                'message' => "Dokter {$doctorName} ditolak dan data dihapus"
            ];
        });

        return $this->successResponse($result, 'Dokter berhasil ditolak');
    }

    /**
     * Get doctor verification status
     */
    public function getDoctorStatus($id)
    {
        $doctor = Dokter::with('user')
            ->findOrFail($id);

        return $this->successResponse([
            'id' => $doctor->id,
            'name' => $doctor->user->name,
            'email' => $doctor->user->email,
            'specialization' => $doctor->specialization,
            'license_number' => $doctor->license_number,
            'is_verified' => $doctor->is_verified,
            'verified_at' => $doctor->verified_at,
            'verification_notes' => $doctor->verification_notes,
        ], 'Doctor verification status');
    }
}
