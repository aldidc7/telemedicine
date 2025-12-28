<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Doctor\StoreDoctorRegistrationRequest;
use App\Models\Dokter;
use App\Services\DoctorRegistrationService;
use App\Services\AdminDoctorVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DoctorRegistrationController extends Controller
{
    public function __construct(
        private DoctorRegistrationService $registrationService,
        private AdminDoctorVerificationService $verificationService
    ) {}

    /**
     * Doctor Self-Registration (SIMPLE)
     * POST /api/v1/dokter/register
     * 
     * Doctor registers with ONLY: name, email, password, phone
     * Waiting for admin to verify documents
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
                'message' => 'Gagal membuat akun dokter.',
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

    // ==================== ADMIN ENDPOINTS ====================

    /**
     * Admin: Get pending doctors for verification
     * GET /api/v1/admin/dokter/pending-verification
     */
    public function getPendingForVerification(Request $request): JsonResponse
    {
        $page = $request->query('page', 1);
        $perPage = $request->query('per_page', 15);

        try {
            $result = $this->verificationService->getPendingDoctors($page, $perPage);

            return response()->json([
                'success' => true,
                'data' => $result['data'],
                'pagination' => $result['pagination'],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mendapatkan daftar dokter pending.',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Admin: Get doctor verification detail
     * GET /api/v1/admin/dokter/{id}/verification-detail
     */
    public function getVerificationDetail($doctorUserId): JsonResponse
    {
        try {
            $detail = $this->verificationService->getDoctorVerificationDetail($doctorUserId);

            return response()->json([
                'success' => true,
                'data' => $detail,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mendapatkan detail verifikasi.',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Admin: Upload documents for a doctor
     * POST /api/v1/admin/dokter/{id}/upload-documents
     * 
     * Admin uploads documents from:
     * - Uploaded files by admin
     * - Database query (KEMENKES)
     * - Physical documents scanned
     */
    public function uploadDocumentsForDoctor($doctorUserId, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sip' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'sip_number' => ['nullable', 'string', 'max:50'],
            'sip_issued_date' => ['nullable', 'date'],
            'sip_expiry_date' => ['nullable', 'date'],
            'str' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'str_number' => ['nullable', 'string', 'max:50'],
            'str_issued_date' => ['nullable', 'date'],
            'str_expiry_date' => ['nullable', 'date'],
            'ktp' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'ijazah' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        try {
            $documents = [];
            foreach ($validated as $key => $value) {
                if ($value && is_object($value) && method_exists($value, 'getMimeType')) {
                    $documents[$key] = $value;
                } elseif ($value && !is_null($value)) {
                    $documents[$key] = $value;
                }
            }

            if (empty($documents)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Minimal upload satu dokumen.',
                ], 400);
            }

            $result = $this->verificationService->uploadDocumentsByAdmin($doctorUserId, $documents);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => [
                    'user_id' => $result['user_id'],
                    'status' => $result['status'],
                    'uploaded_documents' => $result['uploaded_documents'],
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
     * Admin: Set doctor profile data
     * POST /api/v1/admin/dokter/{id}/set-profile
     */
    public function setDoctorProfileData($doctorUserId, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'specialization' => ['required', 'string', 'max:100'],
            'facility_name' => ['nullable', 'string', 'max:255'],
            'kkmi_number' => ['nullable', 'string', 'max:50'],
            'kki_number' => ['nullable', 'string', 'max:50'],
            'sip_number' => ['nullable', 'string', 'max:50'],
            'max_concurrent_consultations' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        try {
            $result = $this->verificationService->setDoctorProfileData($doctorUserId, $validated);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data profil dokter.',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Admin: Approve doctor registration
     * POST /api/v1/admin/dokter/{id}/approve
     */
    public function approveDoctorRegistration($doctorUserId, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $result = $this->verificationService->approveDoctorRegistration(
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
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        try {
            $result = $this->verificationService->rejectDoctorRegistration(
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
