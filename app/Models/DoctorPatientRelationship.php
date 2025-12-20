<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * ============================================
 * MODEL DOCTOR-PATIENT RELATIONSHIP
 * ============================================
 * 
 * Model ini merepresentasikan hubungan dokter-pasien di sistem.
 * Diperlukan untuk compliance dengan Ryan Haight Act.
 * 
 * Fitur:
 * - Track kapan hubungan dokter-pasien dimulai
 * - Track method of establishment (konsultasi, direktif, rujukan, dll)
 * - Track akses pasien ke riwayat dokter
 * - Audit logging semua akses dan perubahan
 * - Soft delete untuk compliance dengan data retention
 * 
 * Ryan Haight Act Compliance:
 * - Harus ada hubungan dokter-pasien sebelum telemedicine
 * - Hubungan harus di-establish melalui sarana yang teridentifikasi
 * - Harus track method of establishment
 * - Harus maintain audit log untuk semua akses
 * 
 * @property int $id
 * @property int $doctor_id - Foreign key ke dokter
 * @property int $patient_id - Foreign key ke pasien
 * @property string $establishment_method - Cara hubungan dimulai
 * @property string $status - Status relationship (active, inactive, terminated)
 * @property \DateTime $established_at - Kapan hubungan dimulai
 * @property \DateTime $last_access_at - Akses terakhir
 * @property \DateTime|null $terminated_at - Kapan hubungan berakhir
 * @property string|null $termination_reason - Alasan hubungan berakhir
 * @property text $notes - Catatan tambahan
 * @property \DateTime|null $deleted_at - Soft delete timestamp
 * @property-read \App\Models\Dokter $doctor
 * @property-read \App\Models\User $patient
 * 
 * @author Aplikasi Telemedicine RSUD dr. R. Soedarsono
 * @version 1.0
 * @date 2025-12-20
 */
class DoctorPatientRelationship extends Model
{
    use HasFactory;

    /**
     * Table name
     */
    protected $table = 'doctor_patient_relationships';

    /**
     * Mass assignable columns
     */
    protected $fillable = [
        'doctor_id',
        'patient_id',
        'establishment_method',
        'status',
        'established_at',
        'last_access_at',
        'terminated_at',
        'termination_reason',
        'notes',
    ];

    /**
     * Date casting
     */
    protected $casts = [
        'established_at' => 'datetime',
        'last_access_at' => 'datetime',
        'terminated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Establishment methods yang valid
     * Ryan Haight Act requires:
     * - Previous consultation
     * - Doctor-initiated
     * - Referral from another doctor
     * - Emergency medical situation
     */
    public const ESTABLISHMENT_METHODS = [
        'consultation' => 'Hasil Konsultasi Sebelumnya',
        'doctor_initiated' => 'Inisiatif Dokter',
        'referral' => 'Rujukan dari Dokter Lain',
        'emergency' => 'Situasi Darurat Medis',
        'patient_request' => 'Permintaan Pasien dengan Verifikasi',
    ];

    /**
     * Status relationship yang valid
     */
    public const STATUSES = [
        'active' => 'Aktif',
        'inactive' => 'Tidak Aktif',
        'suspended' => 'Ditangguhkan',
        'terminated' => 'Berakhir',
    ];

    /**
     * Relationship: Get doctor
     */
    public function doctor()
    {
        return $this->belongsTo(Dokter::class, 'doctor_id');
    }

    /**
     * Relationship: Get patient/user
     */
    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    /**
     * Scope: Get active relationships
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')->whereNull('deleted_at');
    }

    /**
     * Scope: Get relationships for a doctor
     */
    public function scopeForDoctor($query, $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    /**
     * Scope: Get relationships for a patient
     */
    public function scopeForPatient($query, $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    /**
     * Check if relationship is active
     */
    public function isActive()
    {
        return $this->status === 'active' && !$this->deleted_at;
    }

    /**
     * Mark relationship as accessed
     * Used for audit logging
     */
    public function markAsAccessed()
    {
        $this->update(['last_access_at' => now()]);
    }

    /**
     * Terminate relationship
     */
    public function terminate($reason = null)
    {
        $this->update([
            'status' => 'terminated',
            'terminated_at' => now(),
            'termination_reason' => $reason,
        ]);
    }

    /**
     * Get establishment method label
     */
    public function getEstablishmentMethodLabel()
    {
        return self::ESTABLISHMENT_METHODS[$this->establishment_method] ?? $this->establishment_method;
    }

    /**
     * Get status label
     */
    public function getStatusLabel()
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
