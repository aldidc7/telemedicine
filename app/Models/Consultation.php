<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Consultation extends Model
{
    protected $table = 'konsultasis';
    
    protected $fillable = [
        'patient_id',
        'doctor_id',
        'reason',
        'status',
        'scheduled_at',
        'started_at',
        'ended_at',
        'notes',
        'diagnosis',
        'prescription',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    /**
     * Get the patient for this consultation
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Pasien::class, 'patient_id');
    }

    /**
     * Get the doctor for this consultation
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Dokter::class, 'doctor_id');
    }

    /**
     * Get video sessions for this consultation
     */
    public function videoSessions(): HasMany
    {
        return $this->hasMany(VideoSession::class);
    }
}
