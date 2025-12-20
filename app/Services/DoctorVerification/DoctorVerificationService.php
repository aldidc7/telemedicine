<?php

namespace App\Services\DoctorVerification;

use App\Models\DoctorVerification;
use App\Models\DoctorVerificationDocument;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

/**
 * Doctor Verification Service
 * 
 * Handles doctor verification workflow including:
 * - Document upload and validation
 * - Verification status management
 * - Approval/rejection workflow
 */
class DoctorVerificationService
{
    /**
     * Required documents for verification
     */
    const REQUIRED_DOCUMENTS = ['ktp', 'skp', 'sertifikat'];

    /**
     * Maximum file size (5MB)
     */
    const MAX_FILE_SIZE = 5 * 1024 * 1024;

    /**
     * Allowed file extensions
     */
    const ALLOWED_EXTENSIONS = ['pdf', 'jpg', 'jpeg', 'png'];

    /**
     * Start verification process for doctor
     */
    public function submitVerification(User $doctor, array $data): DoctorVerification
    {
        // Check if doctor already has pending verification
        $existing = DoctorVerification::where('doctor_id', $doctor->id)
            ->whereIn('status', ['pending', 'in_review'])
            ->first();

        if ($existing) {
            throw new \Exception('Doctor already has pending verification', 409);
        }

        return DB::transaction(function () use ($doctor, $data) {
            $verification = DoctorVerification::create([
                'doctor_id' => $doctor->id,
                'status' => 'pending',
                'medical_license' => $data['medical_license'] ?? null,
                'specialization' => $data['specialization'] ?? null,
                'institution' => $data['institution'] ?? null,
                'years_of_experience' => $data['years_of_experience'] ?? null,
            ]);

            // Event dispatch removed - use event listeners instead

            return $verification;
        });
    }

    /**
     * Upload verification document
     */
    public function uploadDocument(DoctorVerification $verification, array $data, $file)
    {
        // Validate file
        $this->validateFile($file);

        // Check document type
        if (!in_array($data['document_type'], array_merge(self::REQUIRED_DOCUMENTS, ['lisensi', 'ijazah']))) {
            throw new \Exception('Invalid document type', 422);
        }

        return DB::transaction(function () use ($verification, $data, $file) {
            // Delete existing document of same type
            DoctorVerificationDocument::where('verification_id', $verification->id)
                ->where('document_type', $data['document_type'])
                ->delete();

            // Store file
            $path = Storage::disk('private')->putFileAs(
                "doctor-verification/{$verification->doctor_id}",
                $file,
                $file->hashName()
            );

            $document = DoctorVerificationDocument::create([
                'verification_id' => $verification->id,
                'document_type' => $data['document_type'],
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'status' => 'pending',
            ]);

            // Update verification status if all required docs present
            if ($this->allRequiredDocumentsPresent($verification)) {
                $verification->update(['status' => 'in_review']);
            }

            return $document;
        });
    }

    /**
     * Approve verification
     */
    public function approveVerification(DoctorVerification $verification, array $data = []): DoctorVerification
    {
        // Validate all required documents present
        if (!$this->allRequiredDocumentsPresent($verification)) {
            throw new \Exception('Not all required documents uploaded', 422);
        }

        return DB::transaction(function () use ($verification, $data) {
            $verification->update([
                'status' => 'verified',
                'approved_at' => now(),
                'approved_by' => Auth::id(),
                'approval_notes' => $data['notes'] ?? null,
            ]);

            // Update doctor's verified status
            $verification->doctor->update([
                'verified_at' => now(),
            ]);

            // Send notification to doctor
            Notification::send($verification->doctor, new \App\Notifications\VerificationApprovedNotification($verification));

            return $verification;
        });
    }

    /**
     * Reject verification
     */
    public function rejectVerification(DoctorVerification $verification, array $data): DoctorVerification
    {
        return DB::transaction(function () use ($verification, $data) {
            $verification->update([
                'status' => 'rejected',
                'rejected_at' => now(),
                'rejected_by' => Auth::id(),
                'rejection_reason' => $data['reason'] ?? null,
            ]);

            // Send notification to doctor
            Notification::send($verification->doctor, new \App\Notifications\VerificationRejectedNotification($verification));

            return $verification;
        });
    }

    /**
     * Reset verification (for resubmission after rejection)
    /**
     * Reset verification after rejection
     */
    public function resetVerification(DoctorVerification $verification): DoctorVerification
    {
        return DB::transaction(function () use ($verification) {
            // Delete all uploaded documents
            foreach ($verification->documents as $doc) {
                Storage::disk('private')->delete($doc->file_path);
                $doc->delete();
            }

            $verification->update([
                'status' => 'pending',
                'rejected_at' => null,
                'rejected_by' => null,
                'rejection_reason' => null,
            ]);

            return $verification;
        });
    }

    /**
     * Get verification status
     */
    public function getStatus(User $doctor): ?DoctorVerification
    {
        return DoctorVerification::where('doctor_id', $doctor->id)
            ->with('documents')
            ->latest()
            ->first();
    }

    /**
     * Get document URL for download
     * @noinspection PhpUndefinedMethodInspection
     */
    public function getDocumentUrl(DoctorVerificationDocument $document): string
    {
        // Try temporaryUrl for S3, fallback for local
        try {
            $disk = Storage::disk('private');
            if (method_exists($disk, 'temporaryUrl')) {
                return $disk->temporaryUrl(
                    $document->file_path,
                    now()->addHours(1)
                );
            }
        } catch (\Exception $e) {
            // Fall through
        }

        // Fallback to direct path
        return Storage::disk('private')->path($document->file_path);
    }

    /**
     * Validate file upload
     */
    private function validateFile($file)
    {
        // Validate size
        if ($file->getSize() > self::MAX_FILE_SIZE) {
            throw new \Exception('File size exceeds maximum limit', 422);
        }

        // Validate extension
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, self::ALLOWED_EXTENSIONS)) {
            throw new \Exception('File type not allowed', 422);
        }

        // Validate MIME type
        $mimeType = $file->getMimeType();
        $allowedMimes = [
            'application/pdf',
            'image/jpeg',
            'image/png',
        ];

        if (!in_array($mimeType, $allowedMimes)) {
            throw new \Exception('File MIME type not allowed', 422);
        }
    }

    /**
     * Check if all required documents present
     */
    private function allRequiredDocumentsPresent(DoctorVerification $verification): bool
    {
        $uploadedDocs = $verification->documents()
            ->whereIn('document_type', self::REQUIRED_DOCUMENTS)
            ->pluck('document_type')
            ->toArray();

        return count(array_intersect($uploadedDocs, self::REQUIRED_DOCUMENTS)) === count(self::REQUIRED_DOCUMENTS);
    }
}
