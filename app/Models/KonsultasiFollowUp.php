<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * ============================================
 * MODEL KONSULTASI FOLLOW-UP
 * ============================================
 * 
 * Merepresentasikan follow-up appointment untuk konsultasi.
 * Dokter bisa schedule follow-up di saat membuat summary.
 * 
 * @property int $id
 * @property int $original_consultation_id - Konsultasi asli
 * @property int $follow_up_consultation_id - Konsultasi follow-up (jika sudah dilakukan)
 * @property date $scheduled_date - Tanggal follow-up
 * @property string $status - scheduled, completed, cancelled, no-show
 * @property string $reason - Alasan follow-up
 * 
 * @author Telemedicine App
 */
class KonsultasiFollowUp extends Model
{
    use HasFactory;

    protected $table = 'consultation_follow_ups';

    protected $fillable = [
        'original_consultation_id',
        'follow_up_consultation_id',
        'status',
        'scheduled_date',
        'reason',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ============ RELATIONSHIPS ============

    public function originalKonsultasi(): BelongsTo
    {
        return $this->belongsTo(Konsultasi::class, 'original_consultation_id');
    }

    public function followUpKonsultasi(): HasOne
    {
        return $this->hasOne(Konsultasi::class, 'id', 'follow_up_consultation_id');
    }
}
