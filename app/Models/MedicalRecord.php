<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * ============================================
 * MODEL MEDICAL RECORD - REKAM MEDIS PASIEN
 * ============================================
 * 
 * Menyimpan history medis pasien dari setiap konsultasi/visit
 * Link antara patient, doctor, dan consultation
 * 
 * @property int $id
 * @property int $patient_id - FK ke patients
 * @property int $doctor_id - FK ke dokters
 * @property int $consultation_id - FK ke consultations
 * @property string $diagnosis - Diagnosa
 * @property array $symptoms - Gejala (JSON)
 * @property string $notes - Catatan medis
 * @property array $treatment - Penatalaksanaan (JSON)
 * @property array $prescriptions - Resep obat (JSON)
 * @property string $record_type - Tipe record
 * 
 * @author Aplikasi Telemedicine RSUD dr. R. Soedarsono
 * @version 1.0
 */
class MedicalRecord extends Model
{
    use HasFactory;

    protected $table = 'medical_records';

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'consultation_id',
        'diagnosis',
        'symptoms',
        'notes',
        'treatment',
        'prescriptions',
        'record_type',
    ];

    protected $casts = [
        'symptoms' => 'array',
        'treatment' => 'array',
        'prescriptions' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ===== RELATIONSHIPS =====

    /**
     * Relation to Patient
     */
    public function patient()
    {
        return $this->belongsTo(Pasien::class, 'patient_id');
    }

    /**
     * Relation to Doctor
     */
    public function doctor()
    {
        return $this->belongsTo(Dokter::class, 'doctor_id');
    }

    /**
     * Relation to Consultation
     */
    public function consultation()
    {
        return $this->belongsTo(Konsultasi::class, 'consultation_id');
    }

    // ===== SCOPES =====

    /**
     * Scope: Filter by patient
     */
    public function scopeForPatient($query, $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    /**
     * Scope: Filter by doctor
     */
    public function scopeForDoctor($query, $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    /**
     * Scope: Recent records
     */
    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Scope: Filter by record type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('record_type', $type);
    }
}
