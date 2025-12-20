<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

/**
 * Doctor Verification Document
 * 
 * Menyimpan dokumen-dokumen untuk verifikasi dokter
 * - KTP/Identitas
 * - SKP (Surat Keterangan Pendaftaran)
 * - Sertifikat
 * - Lisensi
 * 
 * @property int $id
 * @property int $dokter_id
 * @property string $document_type - Tipe dokumen (ktp, skp, sertifikat, lisensi, ijazah)
 * @property string $file_path
 * @property string $file_name
 * @property string $mime_type
 * @property int $file_size
 * @property string $status - pending, approved, rejected
 * @property string|null $rejection_reason
 * @property Carbon|null $verified_at
 * @property int|null $verified_by - Admin ID yang verify
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class DoctorVerificationDocument extends Model
{
    protected $table = 'doctor_verification_documents';

    protected $fillable = [
        'dokter_id',
        'document_type',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'status',
        'rejection_reason',
        'verified_at',
        'verified_by',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'file_size' => 'integer',
    ];

    const DOCUMENT_TYPES = [
        'ktp' => 'Kartu Tanda Penduduk',
        'skp' => 'Surat Keterangan Pendaftaran',
        'sertifikat' => 'Sertifikat Keahlian',
        'lisensi' => 'Lisensi Medis',
        'ijazah' => 'Ijazah Pendidikan',
        'asuransi' => 'Surat Asuransi',
    ];

    const STATUS = [
        'pending' => 'Menunggu Verifikasi',
        'approved' => 'Disetujui',
        'rejected' => 'Ditolak',
    ];

    /**
     * Relationship ke Dokter
     */
    public function dokter(): BelongsTo
    {
        return $this->belongsTo(Dokter::class);
    }

    /**
     * Relationship ke User (verified_by)
     */
    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Check if document is approved
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if document is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if document is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Approve document
     */
    public function approve(int $adminId): void
    {
        $this->update([
            'status' => 'approved',
            'verified_by' => $adminId,
            'verified_at' => now(),
            'rejection_reason' => null,
        ]);
    }

    /**
     * Reject document dengan reason
     */
    public function reject(int $adminId, string $reason): void
    {
        $this->update([
            'status' => 'rejected',
            'verified_by' => $adminId,
            'verified_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }

    /**
     * Reset verification status (for re-submission)
     */
    public function resetVerification(): void
    {
        $this->update([
            'status' => 'pending',
            'verified_by' => null,
            'verified_at' => null,
            'rejection_reason' => null,
        ]);
    }
}
