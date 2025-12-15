<?php

namespace App\Services;

use App\Models\User;
use App\Models\Pasien;
use App\Models\Dokter;

/**
 * Profile Completion Service
 * 
 * Track dan manage user profile completion percentage
 */
class ProfileCompletionService
{
    /**
     * Get profile completion percentage
     */
    public static function getCompletion(User $user): array
    {
        if ($user->role === 'pasien') {
            return self::getPasienCompletion($user);
        } elseif ($user->role === 'dokter') {
            return self::getDokterCompletion($user);
        }
        
        return [
            'percentage' => 0,
            'completed_fields' => [],
            'missing_fields' => [],
            'total_fields' => 0,
        ];
    }

    /**
     * Get pasien profile completion
     */
    private static function getPasienCompletion(User $user): array
    {
        $pasien = $user->pasien;
        
        if (!$pasien) {
            return [
                'percentage' => 0,
                'completed_fields' => [],
                'missing_fields' => ['patient_profile'],
                'total_fields' => 1,
            ];
        }

        $totalFields = 10;
        $completedFields = [];
        $missingFields = [];

        // Check each field
        $fields = [
            'name' => $user->name,
            'email' => $user->email,
            'email_verified_at' => $user->email_verified_at,
            'nik' => $pasien->nik,
            'date_of_birth' => $pasien->date_of_birth,
            'gender' => $pasien->gender,
            'phone_number' => $pasien->phone_number,
            'address' => $pasien->address,
            'blood_type' => $pasien->blood_type,
            'emergency_contact' => $pasien->emergency_contact_name,
        ];

        foreach ($fields as $field => $value) {
            if (!empty($value)) {
                $completedFields[] = $field;
            } else {
                $missingFields[] = $field;
            }
        }

        $percentage = round((count($completedFields) / $totalFields) * 100);

        return [
            'percentage' => $percentage,
            'completed_fields' => $completedFields,
            'missing_fields' => $missingFields,
            'total_fields' => $totalFields,
            'completed_count' => count($completedFields),
        ];
    }

    /**
     * Get dokter profile completion
     */
    private static function getDokterCompletion(User $user): array
    {
        $dokter = $user->dokter;
        
        if (!$dokter) {
            return [
                'percentage' => 0,
                'completed_fields' => [],
                'missing_fields' => ['doctor_profile'],
                'total_fields' => 1,
            ];
        }

        $totalFields = 12;
        $completedFields = [];
        $missingFields = [];

        // Check each field
        $fields = [
            'name' => $user->name,
            'email' => $user->email,
            'email_verified_at' => $user->email_verified_at,
            'specialization' => $dokter->specialization,
            'license_number' => $dokter->license_number,
            'phone_number' => $dokter->phone_number,
            'gender' => $dokter->gender,
            'place_of_birth' => $dokter->place_of_birth,
            'birthplace_city' => $dokter->birthplace_city,
            'address' => $dokter->address,
            'profile_photo' => $dokter->profile_photo,
            'is_verified' => $dokter->is_verified,
        ];

        foreach ($fields as $field => $value) {
            if (!empty($value)) {
                $completedFields[] = $field;
            } else {
                $missingFields[] = $field;
            }
        }

        $percentage = round((count($completedFields) / $totalFields) * 100);

        return [
            'percentage' => $percentage,
            'completed_fields' => $completedFields,
            'missing_fields' => $missingFields,
            'total_fields' => $totalFields,
            'completed_count' => count($completedFields),
        ];
    }
}
