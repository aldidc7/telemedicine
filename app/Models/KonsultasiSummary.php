<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * ============================================
 * MODEL KONSULTASI SUMMARY
 * ============================================
 * 
 * Merepresentasikan ringkasan/kesimpulan konsultasi
 * yang dibuat oleh dokter di akhir konsultasi.
 * 
 * @property int $id
 * @property int $consultation_id
 * @property int $doctor_id
 * @property string $diagnosis
 * @property string $clinical_findings
 * @property string $treatment_plan
 * @property date $follow_up_date
 * @property json $medications
 * @property bool $patient_acknowledged
 * @property timestamp $patient_acknowledged_at
 * 
 * @author Telemedicine App
 * @version 1.0
 */
class KonsultasiSummary extends Model
{
    use HasFactory;

    protected $table = 'consultation_summaries';

    protected $fillable = [
        'consultation_id',
        'doctor_id',
        'diagnosis',
        'clinical_findings',
        'examination_results',
        'treatment_plan',
        'follow_up_date',
        'follow_up_instructions',
        'medications',
        'referrals',
        'additional_notes',
        'patient_acknowledged',
        'patient_acknowledged_at',
    ];

    protected $casts = [
        'medications' => 'array',
        'referrals' => 'array',
        'follow_up_date' => 'date',
        'patient_acknowledged_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ============ RELATIONSHIPS ============

    /**
     * Konsultasi yang di-summary
     */
    public function konsultasi(): BelongsTo
    {
        return $this->belongsTo(Konsultasi::class, 'consultation_id');
    }

    /**
     * Dokter yang membuat summary
     */
    public function dokter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    /**
     * Medication details
     */
    public function medications(): HasMany
    {
        return $this->hasMany(KonsultasiMedication::class, 'consultation_id', 'consultation_id');
    }

    /**
     * Follow-up appointments
     */
    public function followUps(): HasMany
    {
        return $this->hasMany(KonsultasiFollowUp::class, 'original_consultation_id', 'consultation_id');
    }
}
