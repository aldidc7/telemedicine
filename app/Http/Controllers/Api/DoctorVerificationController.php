<?php

namespace App\Http\Controllers\Api;

use App\Models\DoctorVerification;
use App\Models\DoctorVerificationDocument;
use App\Services\DoctorVerification\DoctorVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Doctor Verification API Controller
 * 
 * Endpoints for doctor verification workflow
 * - POST /api/v1/doctor-verification/submit - Submit verification
 * - POST /api/v1/doctor-verification/{id}/documents - Upload document
 * - GET /api/v1/doctor-verification/status - Get status
 * - POST /api/v1/doctor-verification/{id}/approve - Approve (Admin)
 * - POST /api/v1/doctor-verification/{id}/reject - Reject (Admin)
 * - GET /api/v1/admin/verifications/pending - List pending (Admin)
 */
class DoctorVerificationController extends BaseApiController
{
    protected DoctorVerificationService $service;

    public function __construct(DoctorVerificationService $service)
    {
        $this->service = $service;
        // Middleware is set in BaseApiController
    }

    /**
     * Submit verification
     * POST /api/v1/doctor-verification/submit
     */
    public function submit(Request $request)
    {
        // Only doctors can submit
        if (!$request->user() || $request->user()->role !== 'doctor') {
            return response()->json([
                'success' => false,
                'message' => 'Only doctors can submit verification',
            ], 403);
        }

        $validated = $request->validate([
            'medical_license' => 'required|string|max:100',
            'specialization' => 'required|string|max:100',
            'institution' => 'nullable|string|max:200',
            'years_of_experience' => 'required|integer|min:0',
        ]);

        try {
            $verification = $this->service->submitVerification($request->user(), $validated);

            return response()->json([
                'success' => true,
                'message' => 'Verification submitted successfully',
                'data' => $verification,
            ], 201);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: 400;
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $statusCode);
        }
    }

    /**
     * Upload verification document
     * POST /api/v1/doctor-verification/{id}/documents
     */
    public function uploadDocument(Request $request, DoctorVerification $verification)
    {
        // Verify ownership
        if ($verification->doctor_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $validated = $request->validate([
            'document_type' => 'required|string|in:ktp,skp,sertifikat,lisensi,ijazah',
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        try {
            $document = $this->service->uploadDocument(
                $verification,
                $validated,
                $request->file('document')
            );

            return response()->json([
                'success' => true,
                'message' => 'Document uploaded successfully',
                'data' => $document,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 400);
        }
    }

    /**
     * Get verification status
     * GET /api/v1/doctor-verification/status
     */
    public function status(Request $request)
    {
        $verification = $this->service->getStatus($request->user());

        if (!$verification) {
            return response()->json([
                'success' => false,
                'message' => 'No verification found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Verification status retrieved',
            'data' => $verification->load('documents'),
        ]);
    }

    /**
     * Get verification details (Admin only)
     * GET /api/v1/doctor-verification/{id}
     */
    public function show(DoctorVerification $verification)
    {
        $user = Auth::user();
        if (!$user || !in_array($user->role, ['admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Verification retrieved',
            'data' => $verification->load('documents', 'doctor'),
        ]);
    }

    /**
     * Approve verification (Admin only)
     * POST /api/v1/doctor-verification/{id}/approve
     */
    public function approve(Request $request, DoctorVerification $verification)
    {
        $user = Auth::user();
        if (!$user || !in_array($user->role, ['admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $verification = $this->service->approveVerification($verification, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Verification approved successfully',
                'data' => $verification,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Reject verification (Admin only)
     * POST /api/v1/doctor-verification/{id}/reject
     */
    public function reject(Request $request, DoctorVerification $verification)
    {
        $user = Auth::user();
        if (!$user || !in_array($user->role, ['admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        try {
            $verification = $this->service->rejectVerification($verification, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Verification rejected',
                'data' => $verification,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Reset verification after rejection
     * POST /api/v1/doctor-verification/{id}/reset
     */
    public function reset(Request $request, DoctorVerification $verification)
    {
        if ($verification->doctor_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        if ($verification->status !== 'rejected') {
            return response()->json([
                'success' => false,
                'message' => 'Can only reset rejected verifications',
            ], 422);
        }

        try {
            $verification = $this->service->resetVerification($verification);

            return response()->json([
                'success' => true,
                'message' => 'Verification reset successfully',
                'data' => $verification,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Download document (Admin and Doctor)
     * GET /api/v1/doctor-verification/documents/{id}/download
     */
    public function downloadDocument(Request $request, DoctorVerificationDocument $document)
    {
        $user = $request->user();

        // Check authorization
        if ($user->id !== $document->verification->doctor_id && $user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        try {
            $url = $this->service->getDocumentUrl($document);

            return response()->json([
                'success' => true,
                'message' => 'Download URL generated',
                'data' => ['url' => $url],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * List pending verifications (Admin only)
     * GET /api/v1/admin/verifications/pending
     */
    public function listPending(Request $request)
    {
        $user = Auth::user();
        if (!$user || !in_array($user->role, ['admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $verifications = DoctorVerification::where('status', 'in_review')
            ->with('doctor', 'documents')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'message' => 'Pending verifications retrieved',
            'data' => $verifications,
        ]);
    }
}
