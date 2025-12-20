<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Doctor\StoreDoctorRegistrationRequest;
use App\Http\Resources\DoctorResource;
use App\Models\Dokter;
use App\Services\DoctorRegistrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DoctorRegistrationController extends Controller
{
    public function __construct(private DoctorRegistrationService $registrationService) {}

    /**
     * Stage 1: Register basic information
     * POST /api/v1/dokter/register
     */
    public function register(StoreDoctorRegistrationRequest $request): JsonResponse
    {
        try {
            $result = $this->registrationService->registerBasicInfo($request->validated());

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => [
                    'user_id' => $result['user_id'],
                    'doctor_id' => $result['doctor_id'],
                    'status' => $result['status'],
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mendaftar dokter.',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Stage 2: Upload verification documents
     * POST /api/v1/dokter/verification/documents
     */
    public function uploadDocuments(Request $request): JsonResponse
    {
        $user = Auth::user();
        if ($user->role !== 'dokter') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya dokter yang dapat mengupload dokumen verifikasi.',
            ], 403);
        }

        try {
            $documents = [];
            $documentTypes = ['sip', 'str', 'ktp', 'ijazah'];

            foreach ($documentTypes as $type) {
                if ($request->hasFile($type)) {
                    $documents[$type] = $request->file($type);
                }
                if ($request->has("{$type}_number")) {
                    $documents["{$type}_number"] = $request->input("{$type}_number");
                }
            }

            if (empty($documents)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Minimal upload satu dokumen verifikasi.',
                ], 400);
            }

            $result = $this->registrationService->uploadDocuments($user->id, $documents);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => [
                    'user_id' => $result['user_id'],
                    'status' => $result['status'],
                    'uploaded_documents' => array_keys($result['uploaded_documents']),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupload dokumen.',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Stage 3: Complete profile information
     * POST /api/v1/dokter/profile/complete
     */
    public function completeProfile(Request $request): JsonResponse
    {
        $user = Auth::user();
        if ($user->role !== 'dokter') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya dokter yang dapat melengkapi profil.',
            ], 403);
        }

        $validated = $request->validate([
            'specialization' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:20'],
            'facility_name' => ['nullable', 'string', 'max:255'],
            'is_available' => ['nullable', 'boolean'],
            'max_concurrent_consultations' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        try {
            $result = $this->registrationService->completeProfile($user->id, $validated);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => [
                    'user_id' => $result['user_id'],
                    'status' => $result['status'],
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal melengkapi profil.',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Stage 4: Accept compliance terms
     * POST /api/v1/dokter/compliance/accept
     */
    public function acceptCompliance(Request $request): JsonResponse
    {
        $user = Auth::user();
        if ($user->role !== 'dokter') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya dokter yang dapat menerima compliance.',
            ], 403);
        }

        $validated = $request->validate([
            'accepted_terms' => ['required', 'boolean'],
            'accepted_privacy' => ['required', 'boolean'],
            'accepted_informed_consent' => ['required', 'boolean'],
        ]);

        try {
            $result = $this->registrationService->acceptCompliance($user->id, $validated);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => [
                    'user_id' => $result['user_id'],
                    'status' => $result['status'],
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menerima compliance.',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get registration status
     * GET /api/v1/dokter/registration/status
     */
    public function getStatus(Request $request): JsonResponse
    {
        $user = Auth::user();

        // Allow doctor to check own status or admin to check any doctor
        $doctorId = $request->query('doctor_id', $user->id);

        if ($user->role !== 'admin' && $doctorId !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda hanya dapat melihat status registrasi Anda sendiri.',
            ], 403);
        }

        try {
            $status = $this->registrationService->getRegistrationStatus($doctorId);

            return response()->json([
                'success' => true,
                'data' => $status,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mendapatkan status registrasi.',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Admin: Get pending doctors for verification
     * GET /api/v1/admin/dokter/pending-verification
     */
    public function getPendingForVerification(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Dokter::class);

        $pendingDoctors = Dokter::whereHas('verificationRecords', function ($query) {
            $query->where('verification_status', 'pending');
        })->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $pendingDoctors,
        ], 200);
    }

    /**
     * Admin: Approve doctor registration
     * POST /api/v1/admin/dokter/{id}/approve
     */
    public function approveDoctorRegistration($doctorUserId, Request $request): JsonResponse
    {
        $this->authorize('update', Dokter::class);

        $validated = $request->validate([
            'notes' => ['nullable', 'string'],
        ]);

        try {
            $result = $this->registrationService->approveDoctorRegistration(
                $doctorUserId,
                Auth::id(),
                $validated['notes'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => [
                    'user_id' => $result['user_id'],
                    'status' => $result['status'],
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyetujui registrasi dokter.',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Admin: Reject doctor registration
     * POST /api/v1/admin/dokter/{id}/reject
     */
    public function rejectDoctorRegistration($doctorUserId, Request $request): JsonResponse
    {
        $this->authorize('update', Dokter::class);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        try {
            $result = $this->registrationService->rejectDoctorRegistration(
                $doctorUserId,
                Auth::id(),
                $validated['reason']
            );

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => [
                    'user_id' => $result['user_id'],
                    'status' => $result['status'],
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menolak registrasi dokter.',
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}
