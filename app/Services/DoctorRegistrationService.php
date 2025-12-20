<?php

namespace App\Services;

use App\Models\User;
use App\Models\Dokter;
use App\Models\DoctorCredential;
use App\Models\DoctorVerification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * DoctorRegistrationService
 * 
 * Manages two-stage doctor registration process:
 * - Stage 1: Basic registration (email, password, name, specialization)
 * - Stage 2: Document upload (SIP, STR, KTP, Ijazah)
 * - Stage 3: Profile completion (profile details, fees, hours)
 * - Stage 4: Compliance acceptance (T&C, privacy, informed consent)
 */
class DoctorRegistrationService
{
    /**
     * Stage 1: Basic Registration
     * Creates user account and doctor profile
     */
    public function registerBasicInfo(array $data): array
    {
        return DB::transaction(function () use ($data) {
            // Create user account
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'phone' => $data['phone'] ?? null,
                'role' => 'dokter',
            ]);

            // Create doctor profile
            $doctor = Dokter::create([
                'user_id' => $user->id,
                'specialization' => $data['specialization'] ?? 'Umum',
                'license_number' => $data['license_number'] ?? 'TEMP_' . Str::random(10),
                'is_available' => false,
                'max_concurrent_consultations' => 5,
            ]);

            // Create doctor verification record (unverified)
            DoctorVerification::create([
                'doctor_id' => $user->id,
                'verification_status' => 'unverified',
                'is_active' => false,
            ]);

            return [
                'user_id' => $user->id,
                'doctor_id' => $doctor->id,
                'status' => 'INCOMPLETE',
                'message' => 'Registrasi dasar berhasil. Silakan upload dokumen verifikasi.',
            ];
        });
    }

    /**
     * Stage 2: Upload Verification Documents
     * Handles SIP, STR, KTP, Ijazah documents
     */
    public function uploadDocuments(int $userId, array $documents): array
    {
        return DB::transaction(function () use ($userId, $documents) {
            $doctor = Dokter::where('user_id', $userId)->firstOrFail();
            $verification = DoctorVerification::where('doctor_id', $userId)->firstOrFail();

            $uploadedDocs = [];

            // Upload SIP (Surat Izin Praktek)
            if (isset($documents['sip'])) {
                $path = $this->uploadDocument($documents['sip'], $userId, 'sip');
                $uploadedDocs['sip'] = $path;

                DoctorCredential::updateOrCreate(
                    ['doctor_id' => $userId, 'credential_type' => 'sip'],
                    [
                        'document_url' => $path,
                        'status' => 'under_review',
                        'credential_number' => $documents['sip_number'] ?? '',
                        'issued_date' => now(),
                        'expiry_date' => now()->addYears(5),
                    ]
                );
            }

            // Upload STR (Surat Tanda Registrasi)
            if (isset($documents['str'])) {
                $path = $this->uploadDocument($documents['str'], $userId, 'str');
                $uploadedDocs['str'] = $path;

                DoctorCredential::updateOrCreate(
                    ['doctor_id' => $userId, 'credential_type' => 'sip'], // STR is related to SIP
                    [
                        'document_url' => $path,
                        'status' => 'under_review',
                        'credential_number' => $documents['str_number'] ?? '',
                        'issued_date' => now(),
                        'expiry_date' => now()->addYears(3),
                    ]
                );
            }

            // Upload KTP (Kartu Tanda Penduduk)
            if (isset($documents['ktp'])) {
                $path = $this->uploadDocument($documents['ktp'], $userId, 'ktp');
                $uploadedDocs['ktp'] = $path;
            }

            // Upload Ijazah (Diploma)
            if (isset($documents['ijazah'])) {
                $path = $this->uploadDocument($documents['ijazah'], $userId, 'ijazah');
                $uploadedDocs['ijazah'] = $path;
            }

            // Update doctor verification status
            $verification->update([
                'verification_status' => 'pending',
            ]);

            return [
                'user_id' => $userId,
                'status' => 'PENDING_VERIFICATION',
                'uploaded_documents' => $uploadedDocs,
                'message' => 'Dokumen berhasil diupload. Menunggu verifikasi admin.',
            ];
        });
    }

    /**
     * Stage 3: Complete Profile Information
     * Add detailed profile information
     */
    public function completeProfile(int $userId, array $data): array
    {
        return DB::transaction(function () use ($userId, $data) {
            $doctor = Dokter::where('user_id', $userId)->firstOrFail();
            $verification = DoctorVerification::where('doctor_id', $userId)->firstOrFail();

            // Update doctor profile
            $doctor->update([
                'specialization' => $data['specialization'] ?? $doctor->specialization,
                'phone_number' => $data['phone'] ?? $doctor->phone_number,
                'is_available' => $data['is_available'] ?? false,
                'max_concurrent_consultations' => $data['max_concurrent_consultations'] ?? 5,
            ]);

            // Update verification with additional info
            $verification->update([
                'specialization' => $data['specialization'] ?? null,
                'facility_name' => $data['facility_name'] ?? null,
            ]);

            return [
                'user_id' => $userId,
                'status' => 'PENDING_VERIFICATION',
                'message' => 'Profil berhasil diperbarui. Menunggu verifikasi admin.',
            ];
        });
    }

    /**
     * Stage 4: Accept Compliance Terms
     * Accept T&C, privacy policy, informed consent
     */
    public function acceptCompliance(int $userId, array $consents): array
    {
        return DB::transaction(function () use ($userId, $consents) {
            $verification = DoctorVerification::where('doctor_id', $userId)->firstOrFail();

            // Verify all required consents are accepted
            if (!($consents['accepted_terms'] ?? false)) {
                throw new InvalidArgumentException('Anda harus menerima syarat dan ketentuan.');
            }

            if (!($consents['accepted_privacy'] ?? false)) {
                throw new InvalidArgumentException('Anda harus menerima kebijakan privasi.');
            }

            if (!($consents['accepted_informed_consent'] ?? false)) {
                throw new InvalidArgumentException('Anda harus menerima informed consent.');
            }

            // Update user email verification if not already verified
            $user = User::find($userId);
            if (!$user->email_verified_at) {
                $user->update(['email_verified_at' => now()]);
            }

            return [
                'user_id' => $userId,
                'status' => 'PENDING_VERIFICATION',
                'message' => 'Compliance diterima. Akun Anda sedang menunggu verifikasi akhir dari admin.',
            ];
        });
    }

    /**
     * Admin Approval: Approve doctor registration
     */
    public function approveDoctorRegistration(int $doctorUserId, int $adminId, ?string $notes = null): array
    {
        return DB::transaction(function () use ($doctorUserId, $adminId, $notes) {
            $doctor = Dokter::where('user_id', $doctorUserId)->firstOrFail();
            $verification = DoctorVerification::where('doctor_id', $doctorUserId)->firstOrFail();
            $user = User::find($doctorUserId);

            // Update doctor status
            $doctor->update([
                'is_verified' => true,
                'verified_at' => now(),
                'verified_by_admin_id' => $adminId,
                'verification_notes' => $notes,
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

            return [
                'user_id' => $doctorUserId,
                'status' => 'ACTIVE',
                'message' => 'Dokter berhasil diverifikasi dan aktif.',
            ];
        });
    }

    /**
     * Admin Rejection: Reject doctor registration
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

            return [
                'user_id' => $doctorUserId,
                'status' => 'REJECTED',
                'message' => 'Registrasi dokter ditolak. Hubungi admin untuk informasi lebih lanjut.',
            ];
        });
    }

    /**
     * Get doctor registration status
     */
    public function getRegistrationStatus(int $userId): array
    {
        $doctor = Dokter::where('user_id', $userId)->firstOrFail();
        $verification = DoctorVerification::where('doctor_id', $userId)->firstOrFail();
        $credentials = DoctorCredential::where('doctor_id', $userId)->get();

        return [
            'user_id' => $userId,
            'registration_status' => $this->determineRegistrationStatus($doctor, $verification),
            'is_verified' => $doctor->is_verified ?? false,
            'is_active' => $verification->is_active ?? false,
            'verification_status' => $verification->verification_status ?? 'unverified',
            'credentials' => $credentials->map(fn($c) => [
                'type' => $c->credential_type,
                'number' => $c->credential_number,
                'status' => $c->status,
            ]),
            'verified_at' => $doctor->verified_at,
            'verified_by' => $doctor->verified_by_admin_id,
            'notes' => $doctor->verification_notes,
        ];
    }

    /**
     * Determine current registration status
     */
    private function determineRegistrationStatus(Dokter $doctor, DoctorVerification $verification): string
    {
        if ($verification->verification_status === 'verified' && $verification->is_active) {
            return 'ACTIVE';
        }

        if ($verification->verification_status === 'rejected') {
            return 'REJECTED';
        }

        if ($verification->verification_status === 'pending') {
            return 'PENDING_VERIFICATION';
        }

        return 'INCOMPLETE';
    }

    /**
     * Upload and validate document
     */
    private function uploadDocument($file, int $userId, string $docType): string
    {
        // Validate file
        $this->validateDocument($file, $docType);

        // Generate unique filename
        $filename = "doctors/{$userId}/{$docType}_" . time() . '.' . $file->getClientOriginalExtension();

        // Store file
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
