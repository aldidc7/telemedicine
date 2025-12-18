<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

/**
 * ============================================
 * PATIENT SECURITY SERVICE
 * ============================================
 * 
 * Handle:
 * - MRN (Medical Record Number) generation
 * - NIK encryption/decryption
 * - PII security
 * 
 * @author Aplikasi Telemedicine RSUD dr. R. Soedarsono
 * @version 1.0
 */
class PatientSecurityService
{
    /**
     * Generate MRN (Medical Record Number)
     * Format: RM-YYYY-XXXXX
     * 
     * Example: RM-2024-00001, RM-2024-00142
     * 
     * @param int|null $sequence - Optional sequence number
     * @return string Generated MRN
     */
    public static function generateMRN($sequence = null): string
    {
        $year = now()->year;
        
        // Generate sequence jika tidak diberikan
        if ($sequence === null) {
            // Query latest MRN dan increment
            $latest = \App\Models\Pasien::where('medical_record_number', 'like', "RM-{$year}-%")
                ->latest('id')
                ->first();
            
            if ($latest && preg_match('/RM-\d{4}-(\d+)/', $latest->medical_record_number, $matches)) {
                $sequence = (int)$matches[1] + 1;
            } else {
                $sequence = 1;
            }
        }
        
        // Format: RM-2024-00001
        return sprintf('RM-%d-%05d', $year, $sequence);
    }

    /**
     * Encrypt NIK (Nomor Identitas Kependudukan)
     * NIK adalah PII dan harus dienkripsi
     * 
     * @param string $nik - Raw NIK
     * @return string Encrypted NIK
     */
    public static function encryptNIK($nik): string
    {
        if (empty($nik)) {
            return null;
        }
        
        try {
            return Crypt::encryptString($nik);
        } catch (\Exception $e) {
            \Log::error('Failed to encrypt NIK: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Decrypt NIK
     * 
     * @param string $encryptedNIK - Encrypted NIK
     * @return string|null Decrypted NIK atau null jika gagal
     */
    public static function decryptNIK($encryptedNIK): ?string
    {
        if (empty($encryptedNIK)) {
            return null;
        }
        
        try {
            return Crypt::decryptString($encryptedNIK);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            \Log::warning('Failed to decrypt NIK: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Mask NIK untuk display (show last 4 digits only)
     * 
     * @param string $nik - Raw NIK
     * @return string Masked NIK (e.g., "XXXXXXXXXXXX1234")
     */
    public static function maskNIK($nik): string
    {
        if (strlen($nik) < 4) {
            return str_repeat('X', strlen($nik));
        }
        
        $masked = str_repeat('X', strlen($nik) - 4) . substr($nik, -4);
        return $masked;
    }

    /**
     * Validate NIK format (16 digits)
     * 
     * @param string $nik - NIK to validate
     * @return bool True jika valid NIK format
     */
    public static function isValidNIK($nik): bool
    {
        return preg_match('/^\d{16}$/', $nik) === 1;
    }
}
