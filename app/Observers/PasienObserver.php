<?php

namespace App\Observers;

use App\Models\Pasien;
use App\Services\PatientSecurityService;

/**
 * ============================================
 * PATIENT OBSERVER - AUTO ACTIONS
 * ============================================
 * 
 * Handle automatic operations when Patient is created/updated:
 * - Generate MRN otomatis saat patient baru
 * - Encrypt NIK saat disimpan
 * - Log access ke audit_logs
 */
class PasienObserver
{
    /**
     * Handle the Pasien "creating" event.
     * Dipanggil SEBELUM patient disimpan ke database
     */
    public function creating(Pasien $pasien): void
    {
        // Generate MRN jika belum ada
        if (empty($pasien->medical_record_number)) {
            $pasien->medical_record_number = PatientSecurityService::generateMRN();
        }

        // Encrypt NIK jika ada (assuming ada field 'nik' di request)
        // Note: Field 'nik' yang disubmit akan di-encrypt menjadi 'encrypted_nik'
        if (isset($pasien->attributes['nik']) && !empty($pasien->attributes['nik'])) {
            $plainNIK = $pasien->attributes['nik'];
            $pasien->encrypted_nik = PatientSecurityService::encryptNIK($plainNIK);
            // Hapus field nik yang plain text, simpan hanya encrypted version
            unset($pasien->attributes['nik']);
        }
    }

    /**
     * Handle the Pasien "created" event.
     * Dipanggil SETELAH patient berhasil disimpan ke database
     */
    public function created(Pasien $pasien): void
    {
        // Log patient creation untuk audit trail
        \App\Models\AuditLog::logPatientAccess(
            auth()->id() ?? null,
            (int) $pasien->id,
            'create',
            "Patient baru dibuat: {$pasien->medical_record_number}"
        );
    }

    /**
     * Handle the Pasien "updating" event.
     * Dipanggil SEBELUM patient diupdate
     */
    public function updating(Pasien $pasien): void
    {
        // Encrypt NIK jika diubah
        if ($pasien->isDirty('nik')) {
            $plainNIK = $pasien->getAttribute('nik');
            if (!empty($plainNIK)) {
                $pasien->encrypted_nik = PatientSecurityService::encryptNIK($plainNIK);
                // Hapus field plain text
                $pasien->attributes['nik'] = null;
            }
        }
    }

    /**
     * Handle the Pasien "updated" event.
     * Dipanggil SETELAH patient berhasil diupdate
     */
    public function updated(Pasien $pasien): void
    {
        // Log changes untuk audit trail
        $changes = $pasien->getChanges();
        
        // Don't log encrypted_nik changes untuk security
        unset($changes['encrypted_nik']);
        unset($changes['updated_at']);
        
        if (!empty($changes)) {
            \App\Models\AuditLog::logPatientAccess(
                auth()->id() ?? null,
                (int) $pasien->id,
                'update',
                "Patient diupdate: " . json_encode(array_keys($changes))
            );
        }
    }

    /**
     * Handle the Pasien "deleted" event.
     */
    public function deleted(Pasien $pasien): void
    {
        \App\Models\AuditLog::logPatientAccess(
            auth()->id() ?? null,
            (int) $pasien->id,
            'delete',
            "Patient dihapus: {$pasien->medical_record_number}"
        );
    }
}
