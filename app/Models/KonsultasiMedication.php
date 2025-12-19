<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ============================================
 * MODEL KONSULTASI MEDICATION
 * ============================================
 * 
 * Merepresentasikan resep obat dalam konsultasi.
 * 
 * @property int $id
 * @property int $consultation_id
 * @property int $doctor_id
 * @property string $medication_name - Nama obat
 * @property string $dose - Dosis
 * @property string $frequency - Frekuensi (3x sehari, dll)
 * @property int $duration_days - Durasi pengobatan
 * @property string $route - Rute pemberian (oral, injection, dll)
 * @property string $status - prescribed, filled, completed
 * 
 * @author Telemedicine App
 */
class KonsultasiMedication extends Model
{
    use HasFactory;

    protected $table = 'consultation_medications';

    protected $fillable = [
        'consultation_id',
        'doctor_id',
        'medication_name',
        'dose',
        'frequency',
        'duration_days',
        'instructions',
        'route',
        'is_active',
        'status',
        'prescribed_at',
        'filled_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'prescribed_at' => 'datetime',
        'filled_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ============ RELATIONSHIPS ============

    public function konsultasi(): BelongsTo
    {
        return $this->belongsTo(Konsultasi::class, 'consultation_id');
    }

    public function dokter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }
}
