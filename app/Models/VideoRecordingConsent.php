<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * VideoRecordingConsent Model
 * 
 * Menyimpan consent pasien untuk recording video consultation
 * GDPR & privacy compliance requirement
 */
class VideoRecordingConsent extends Model
{
    protected $table = 'video_recording_consents';

    protected $fillable = [
        'consultation_id',
        'patient_id',
        'doctor_id',
        'consented_to_recording',
        'consent_reason',
        'ip_address',
        'user_agent',
        'consent_given_at',
        'withdrawn_at',
    ];

    protected $casts = [
        'consented_to_recording' => 'boolean',
        'consent_given_at' => 'datetime',
        'withdrawn_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship: Belongs to Konsultasi
     */
    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Konsultasi::class, 'consultation_id');
    }

    /**
     * Relationship: Belongs to Patient (User)
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    /**
     * Relationship: Belongs to Doctor (User)
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    /**
     * Check if consent is active (not withdrawn)
     * 
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->consented_to_recording && !$this->withdrawn_at;
    }

    /**
     * Withdraw consent
     * 
     * @return void
     */
    public function withdraw(): void
    {
        $this->update(['withdrawn_at' => now()]);
    }

    /**
     * Get consent status text
     * 
     * @return string
     */
    public function getStatusText(): string
    {
        if (!$this->consented_to_recording) {
            return 'Tidak Menyetujui';
        }

        if ($this->withdrawn_at) {
            return 'Ditarik (Withdrawn)';
        }

        return 'Menyetujui';
    }
}
