<?php

namespace App\Services;

use App\Models\User;
use App\Models\Dokter;
use App\Models\DoctorVerification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * DoctorRegistrationService
 * 
 * Manages doctor self-registration (SIMPLE & SECURE):
 * - Doctor registers with basic info ONLY (email, password, name, phone)
 * - Admin verifies documents and activates the account
 * - This prevents dokter palsu (fake doctors) from registering
 */
class DoctorRegistrationService
{
    /**
     * Stage 1: Doctor Self-Registration
     * Creates user account and doctor profile in PENDING state
     * Only: Name, Email, Password, Phone
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
                'email_verified_at' => now(), // Auto-verify email
            ]);

            // Create doctor profile (pending admin verification)
            $doctor = Dokter::create([
                'user_id' => $user->id,
                'specialization' => 'Menunggu Verifikasi Admin', // Placeholder
                'license_number' => 'TEMP_' . now()->timestamp,
                'is_available' => false, // Cannot accept patients yet
                'max_concurrent_consultations' => 5,
                'is_verified' => false,
            ]);

            // Create verification record
            DoctorVerification::create([
                'doctor_id' => $user->id,
                'verification_status' => 'unverified',
                'is_active' => false,
            ]);

            return [
                'user_id' => $user->id,
                'doctor_id' => $doctor->id,
                'status' => 'PENDING_VERIFICATION',
                'message' => 'Akun terbuat. Menunggu admin melakukan verifikasi dokumen Anda.',
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

        return [
            'user_id' => $userId,
            'registration_status' => $this->determineRegistrationStatus($doctor, $verification),
            'is_verified' => $doctor->is_verified ?? false,
            'is_active' => $verification->is_active ?? false,
            'verification_status' => $verification->verification_status ?? 'unverified',
            'specialization' => $doctor->specialization ?? null,
            'facility_name' => $verification->facility_name ?? null,
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

        return 'PENDING_VERIFICATION';
    }
}
