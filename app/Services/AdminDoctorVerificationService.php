<?php

namespace App\Services;

use App\Models\User;
use App\Models\Dokter;
use App\Models\DoctorCredential;
use App\Models\DoctorVerification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

/**
 * AdminDoctorVerificationService
 * 
 * Manages admin-side doctor verification:
 * - Admin uploads documents from verified sources
 * - Admin sets specialization from credentials
 * - Admin approves or rejects doctors
 * 
 * This ensures only verified doctors can practice (Zero dokter palsu)
 */
class AdminDoctorVerificationService
{
    /**
     * Admin: Upload verification documents for a doctor
     * Documents can come from:
     * - Uploaded files by admin
     * - Database query (KEMENKES API, if available)
     * - Physical documents scanned by admin
     */
    public function uploadDocumentsByAdmin(int $doctorUserId, array $documents): array
    {
        return DB::transaction(function () use ($doctorUserId, $documents) {
            $doctor = Dokter::where('user_id', $doctorUserId)->firstOrFail();

            $uploadedDocs = [];

            // Upload SIP (Surat Izin Praktek)
            if (isset($documents['sip'])) {
                $path = $this->uploadDocument($documents['sip'], $doctorUserId, 'sip');
                $uploadedDocs['sip'] = $path;

                DoctorCredential::updateOrCreate(
                    ['doctor_id' => $doctorUserId, 'credential_type' => 'sip'],
                    [
                        'document_url' => $path,
                        'status' => 'under_review',
                        'credential_number' => $documents['sip_number'] ?? '',
                        'issued_date' => $documents['sip_issued_date'] ?? now(),
                        'expiry_date' => $documents['sip_expiry_date'] ?? now()->addYears(5),
                    ]
                );
            }

            // Upload STR (Surat Tanda Registrasi)
            if (isset($documents['str'])) {
                $path = $this->uploadDocument($documents['str'], $doctorUserId, 'str');
                $uploadedDocs['str'] = $path;

                DoctorCredential::updateOrCreate(
                    ['doctor_id' => $doctorUserId, 'credential_type' => 'str'],
                    [
                        'document_url' => $path,
                        'status' => 'under_review',
                        'credential_number' => $documents['str_number'] ?? '',
                        'issued_date' => $documents['str_issued_date'] ?? now(),
                        'expiry_date' => $documents['str_expiry_date'] ?? now()->addYears(3),
                    ]
                );
            }

            // Upload KTP (Kartu Tanda Penduduk)
            if (isset($documents['ktp'])) {
                $path = $this->uploadDocument($documents['ktp'], $doctorUserId, 'ktp');
                $uploadedDocs['ktp'] = $path;
            }

            // Upload Ijazah (Diploma)
            if (isset($documents['ijazah'])) {
                $path = $this->uploadDocument($documents['ijazah'], $doctorUserId, 'ijazah');
                $uploadedDocs['ijazah'] = $path;
            }

            // Update doctor verification status to pending review
            $verification = DoctorVerification::where('doctor_id', $doctorUserId)->first();
            if ($verification) {
                $verification->update([
                    'verification_status' => 'pending',
                ]);
            }

            return [
                'user_id' => $doctorUserId,
                'status' => 'PENDING_VERIFICATION',
                'uploaded_documents' => $uploadedDocs,
                'message' => 'Dokumen berhasil diupload. Lanjutkan dengan verifikasi.',
            ];
        });
    }

    /**
     * Admin: Verify and set doctor profile data
     * Admin sets:
     * - Specialization (from credentials)
     * - Facility name (where doctor practices)
     * - Max concurrent consultations
     */
    public function setDoctorProfileData(int $doctorUserId, array $data): array
    {
        return DB::transaction(function () use ($doctorUserId, $data) {
            $doctor = Dokter::where('user_id', $doctorUserId)->firstOrFail();
            $verification = DoctorVerification::where('doctor_id', $doctorUserId)->firstOrFail();

            // Admin sets specialization from credentials
            if (isset($data['specialization'])) {
                $doctor->update([
                    'specialization' => $data['specialization'],
                ]);
            }

            // Update verification data
            $verification->update([
                'specialization' => $data['specialization'] ?? null,
                'facility_name' => $data['facility_name'] ?? null,
                'kkmi_number' => $data['kkmi_number'] ?? null,
                'kki_number' => $data['kki_number'] ?? null,
                'sip_number' => $data['sip_number'] ?? null,
            ]);

            // Update max consultations if provided
            if (isset($data['max_concurrent_consultations'])) {
                $doctor->update([
                    'max_concurrent_consultations' => $data['max_concurrent_consultations'],
                ]);
            }

            return [
                'user_id' => $doctorUserId,
                'message' => 'Data profil dokter berhasil diatur.',
            ];
        });
    }

    /**
     * Admin: Approve doctor registration
     * Doctor becomes ACTIVE and can start receiving consultations
     */
    public function approveDoctorRegistration(int $doctorUserId, int $adminId, ?string $notes = null): array
    {
        return DB::transaction(function () use ($doctorUserId, $adminId, $notes) {
            $doctor = Dokter::where('user_id', $doctorUserId)->firstOrFail();
            $verification = DoctorVerification::where('doctor_id', $doctorUserId)->firstOrFail();

            // Update doctor status
            $doctor->update([
                'is_verified' => true,
                'verified_at' => now(),
                'verified_by_admin_id' => $adminId,
                'verification_notes' => $notes,
                'is_available' => true, // Make available for consultations
            ]);

            // Update verification status
            $verification->update([
                'verification_status' => 'verified',
                'verified_at' => now(),
                'verified_by' => $adminId,
                'is_active' => true,
                'notes' => $notes,
            ]);

            // Update credentials status
            DoctorCredential::where('doctor_id', $doctorUserId)
                ->update(['status' => 'verified']);

            // Send approval notification to doctor
            try {
                $user = User::find($doctorUserId);
                if ($user) {
                    \Illuminate\Support\Facades\Notification::send(
                        $user,
                        new \App\Notifications\VerificationApprovedNotification($verification)
                    );
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Failed to send approval notification', [
                    'doctor_id' => $doctorUserId,
                ]);
            }

            return [
                'user_id' => $doctorUserId,
                'status' => 'ACTIVE',
                'message' => 'Dokter berhasil diverifikasi dan aktif. Bisa menerima konsultasi sekarang.',
            ];
        });
    }

    /**
     * Admin: Reject doctor registration
     * Doctor cannot practice, must contact admin or reapply
     */
    public function rejectDoctorRegistration(int $doctorUserId, int $adminId, string $reason): array
    {
        return DB::transaction(function () use ($doctorUserId, $adminId, $reason) {
            $doctor = Dokter::where('user_id', $doctorUserId)->firstOrFail();
            $verification = DoctorVerification::where('doctor_id', $doctorUserId)->firstOrFail();

            // Update doctor status
            $doctor->update([
                'is_verified' => false,
                'verified_by_admin_id' => $adminId,
                'verification_notes' => $reason,
                'is_available' => false,
            ]);

            // Update verification status
            $verification->update([
                'verification_status' => 'rejected',
                'verified_by' => $adminId,
                'is_active' => false,
                'notes' => $reason,
            ]);

            // Update credentials status
            DoctorCredential::where('doctor_id', $doctorUserId)
                ->update(['status' => 'rejected', 'rejection_reason' => $reason]);

            // Send rejection notification to doctor
            try {
                $user = User::find($doctorUserId);
                if ($user) {
                    \Illuminate\Support\Facades\Notification::send(
                        $user,
                        new \App\Notifications\VerificationRejectedNotification($verification)
                    );
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Failed to send rejection notification', [
                    'doctor_id' => $doctorUserId,
                ]);
            }

            return [
                'user_id' => $doctorUserId,
                'status' => 'REJECTED',
                'message' => 'Registrasi dokter ditolak.',
            ];
        });
    }

    /**
     * Get list of pending verification doctors for admin
     */
    public function getPendingDoctors($page = 1, $perPage = 15): array
    {
        $pendingDoctors = Dokter::with(['user', 'verificationRecords'])
            ->whereHas('verificationRecords', function ($query) {
                $query->where('verification_status', 'unverified')
                    ->orWhere('verification_status', 'pending');
            })
            ->paginate($perPage, ['*'], 'page', $page);

        return [
            'data' => $pendingDoctors->items(),
            'pagination' => [
                'total' => $pendingDoctors->total(),
                'per_page' => $pendingDoctors->perPage(),
                'current_page' => $pendingDoctors->currentPage(),
                'last_page' => $pendingDoctors->lastPage(),
            ],
        ];
    }

    /**
     * Get doctor verification detail for admin
     */
    public function getDoctorVerificationDetail(int $doctorUserId): array
    {
        $doctor = Dokter::where('user_id', $doctorUserId)
            ->with(['user', 'verificationRecords', 'credentials'])
            ->firstOrFail();

        return [
            'id' => $doctor->id,
            'user_id' => $doctor->user_id,
            'name' => $doctor->user->name,
            'email' => $doctor->user->email,
            'phone' => $doctor->user->phone,
            'specialization' => $doctor->specialization,
            'license_number' => $doctor->license_number,
            'verification_status' => $doctor->verificationRecords?->verification_status ?? 'unverified',
            'is_verified' => $doctor->is_verified,
            'verified_at' => $doctor->verified_at,
            'credentials' => $doctor->credentials->map(fn($c) => [
                'type' => $c->credential_type,
                'number' => $c->credential_number,
                'document_url' => $c->document_url,
                'status' => $c->status,
                'issued_date' => $c->issued_date,
                'expiry_date' => $c->expiry_date,
            ]),
            'notes' => $doctor->verification_notes,
        ];
    }

    /**
     * Upload and validate document
     */
    private function uploadDocument($file, int $userId, string $docType): string
    {
        $this->validateDocument($file, $docType);

        $filename = "doctors/{$userId}/{$docType}_" . time() . '.' . $file->getClientOriginalExtension();
        $path = Storage::disk('private')->put($filename, file_get_contents($file));

        return $path ?: throw new InvalidArgumentException("Gagal mengupload dokumen {$docType}");
    }

    /**
     * Validate document file
     */
    private function validateDocument($file, string $docType): void
    {
        $maxSize = 5 * 1024 * 1024; // 5MB
        $allowedMimes = ['image/jpeg', 'image/png', 'application/pdf'];

        if ($file->getSize() > $maxSize) {
            throw new InvalidArgumentException("Ukuran file {$docType} terlalu besar. Maksimal 5MB.");
        }

        if (!in_array($file->getMimeType(), $allowedMimes)) {
            throw new InvalidArgumentException("Format file {$docType} tidak didukung. Gunakan JPG, PNG, atau PDF.");
        }
    }
}
