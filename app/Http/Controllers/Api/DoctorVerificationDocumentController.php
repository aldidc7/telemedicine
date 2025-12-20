<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponseFormatter;
use App\Models\Dokter;
use App\Models\DoctorVerificationDocument;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * Doctor Verification Document Controller
 * 
 * Menangani upload dan verifikasi dokumen dokter
 * - Upload dokumen
 * - List dokumen
 * - Approve/Reject dokumen
 * - Re-submit dokumen
 */
class DoctorVerificationDocumentController extends Controller
{
    /**
     * Upload verification document
     * 
     * POST /api/v1/doctor/verification/upload
     */
    public function upload(Request $request): JsonResponse
    {
        $user = Auth::user();
        $dokter = Dokter::where('user_id', $user->id)->first();

        if (!$dokter) {
            return ApiResponseFormatter::notFound('Doctor profile not found');
        }

        $validated = $request->validate([
            'document_type' => [
                'required',
                'string',
                Rule::in(array_keys(DoctorVerificationDocument::DOCUMENT_TYPES)),
            ],
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240', // 10MB max
        ]);

        // Check if document already exists and pending/rejected
        $existingDoc = DoctorVerificationDocument::where('dokter_id', $dokter->id)
            ->where('document_type', $validated['document_type'])
            ->first();

        if ($existingDoc && $existingDoc->status === 'pending') {
            return ApiResponseFormatter::conflict('Document is still pending verification. Please wait.');
        }

        // Upload file to storage
        $file = $request->file('file');
        $fileName = time() . '_' . $dokter->id . '_' . $validated['document_type'] . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs('doctor-verification', $fileName, 'private');

        // Create or update document record
        if ($existingDoc) {
            $existingDoc->update([
                'file_path' => $filePath,
                'file_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'status' => 'pending',
                'rejection_reason' => null,
                'verified_by' => null,
                'verified_at' => null,
            ]);
            $document = $existingDoc;
        } else {
            $document = DoctorVerificationDocument::create([
                'dokter_id' => $dokter->id,
                'document_type' => $validated['document_type'],
                'file_path' => $filePath,
                'file_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'status' => 'pending',
            ]);
        }

        return ApiResponseFormatter::created(
            $this->formatDocument($document),
            'Document uploaded successfully'
        );
    }

    /**
     * Get doctor's verification documents
     * 
     * GET /api/v1/doctor/verification/documents
     */
    public function getDocuments(): JsonResponse
    {
        $user = Auth::user();
        $dokter = Dokter::where('user_id', $user->id)->first();

        if (!$dokter) {
            return ApiResponseFormatter::notFound('Doctor profile not found');
        }

        $documents = DoctorVerificationDocument::where('dokter_id', $dokter->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($doc) => $this->formatDocument($doc));

        return ApiResponseFormatter::success($documents, 'Doctor verification documents');
    }

    /**
     * Get verification status
     * 
     * GET /api/v1/doctor/verification/status
     */
    public function getVerificationStatus(): JsonResponse
    {
        $user = Auth::user();
        $dokter = Dokter::where('user_id', $user->id)->first();

        if (!$dokter) {
            return ApiResponseFormatter::notFound('Doctor profile not found');
        }

        $documents = DoctorVerificationDocument::where('dokter_id', $dokter->id)->get();

        $status = [
            'is_fully_verified' => $dokter->is_verified,
            'overall_status' => $this->getOverallStatus($documents),
            'pending_documents' => $documents->where('status', 'pending')->count(),
            'approved_documents' => $documents->where('status', 'approved')->count(),
            'rejected_documents' => $documents->where('status', 'rejected')->count(),
            'required_documents' => array_keys(DoctorVerificationDocument::DOCUMENT_TYPES),
            'documents' => $documents->map(fn($doc) => $this->formatDocument($doc)),
        ];

        return ApiResponseFormatter::success($status, 'Verification status');
    }

    /**
     * Admin: List pending documents for verification
     * 
     * GET /api/v1/admin/verification/pending
     */
    public function adminListPending(Request $request): JsonResponse
    {
        $documents = DoctorVerificationDocument::where('status', 'pending')
            ->with(['dokter.user', 'dokter'])
            ->paginate($request->get('per_page', 20));

        return ApiResponseFormatter::paginated(
            $documents->items(),
            [
                'total' => $documents->total(),
                'per_page' => $documents->perPage(),
                'current_page' => $documents->currentPage(),
                'last_page' => $documents->lastPage(),
            ],
            'Pending verification documents'
        );
    }

    /**
     * Admin: Approve document
     * 
     * POST /api/v1/admin/verification/{id}/approve
     */
    public function adminApprove(int $documentId): JsonResponse
    {
        $document = DoctorVerificationDocument::find($documentId);

        if (!$document) {
            return ApiResponseFormatter::notFound('Document not found');
        }

        $document->approve(Auth::id());

        // Check if all required documents are approved
        $dokter = $document->dokter;
        $approvedCount = DoctorVerificationDocument::where('dokter_id', $dokter->id)
            ->where('status', 'approved')
            ->count();

        if ($approvedCount >= count(DoctorVerificationDocument::DOCUMENT_TYPES)) {
            // Mark doctor as verified
            $dokter->update(['is_verified' => true]);
        }

        return ApiResponseFormatter::success(
            $this->formatDocument($document),
            'Document approved'
        );
    }

    /**
     * Admin: Reject document
     * 
     * POST /api/v1/admin/verification/{id}/reject
     */
    public function adminReject(int $documentId, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $document = DoctorVerificationDocument::find($documentId);

        if (!$document) {
            return ApiResponseFormatter::notFound('Document not found');
        }

        $document->reject(Auth::id(), $validated['reason']);

        // Remove doctor verification status if previously verified
        $dokter = $document->dokter;
        if ($dokter->is_verified) {
            $dokter->update(['is_verified' => false]);
        }

        return ApiResponseFormatter::success(
            $this->formatDocument($document),
            'Document rejected'
        );
    }

    /**
     * Download document (for admin/doctor)
     * 
     * GET /api/v1/verification/{id}/download
     */
    public function download(int $documentId): \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\Response
    {
        $document = DoctorVerificationDocument::find($documentId);

        if (!$document) {
            return response('Document not found', 404);
        }

        // Check authorization
        $user = Auth::user();
        $dokter = Dokter::where('user_id', $user->id)->first();

        if ($user->role !== 'admin' && $dokter?->id !== $document->dokter_id) {
            return response('Unauthorized', 403);
        }

        $disk = Storage::disk('private');
        
        if (!$disk->exists($document->file_path)) {
            return response('File not found', 404);
        }

        return $disk->download($document->file_path, $document->file_name);
    }

    // ============ HELPER METHODS ============

    private function formatDocument(DoctorVerificationDocument $document): array
    {
        return [
            'id' => $document->id,
            'dokter_id' => $document->dokter_id,
            'document_type' => $document->document_type,
            'document_type_label' => DoctorVerificationDocument::DOCUMENT_TYPES[$document->document_type] ?? $document->document_type,
            'file_name' => $document->file_name,
            'file_size' => $document->file_size,
            'status' => $document->status,
            'status_label' => DoctorVerificationDocument::STATUS[$document->status] ?? $document->status,
            'rejection_reason' => $document->rejection_reason,
            'verified_at' => $document->verified_at?->toIso8601String(),
            'created_at' => $document->created_at->toIso8601String(),
            'updated_at' => $document->updated_at->toIso8601String(),
        ];
    }

    private function getOverallStatus($documents): string
    {
        if ($documents->isEmpty()) {
            return 'no_documents';
        }

        $rejectedCount = $documents->where('status', 'rejected')->count();
        if ($rejectedCount > 0) {
            return 'has_rejected';
        }

        $pendingCount = $documents->where('status', 'pending')->count();
        if ($pendingCount > 0) {
            return 'pending_review';
        }

        return 'all_approved';
    }
}
