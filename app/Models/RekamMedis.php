<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * ============================================
 * MODEL REKAMMEDIS - DATA REKAM MEDIS
 * ============================================
 * 
 * Model ini merepresentasikan tabel 'medical_records' di database.
 * Menyimpan riwayat medis dan rekam medis pasien dari SIMRS atau lokal.
 * 
 * @property int $id - ID rekam medis
 * @property int $pasien_id - Foreign key ke patients
 * @property string $tipe_record - Tipe record (riwayat, diagnosis, dll)
 * @property array $data - Data JSON
 * @property \Date $tgl_record - Tanggal record
 * @property string $sumber - Sumber (SIMRS/LOKAL)
 * 
 * @author Aplikasi Telemedicine
 * @version 1.0
 */
class RekamMedis extends Model
{
    use HasFactory;

    protected $table = 'medical_records';

    protected $fillable = [
        'patient_id',
        'record_type',
        'data',
        'record_date',
        'source',
        'notes',
    ];

    protected $casts = [
        'data' => 'array',
        'record_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ============ RELATIONSHIPS ============
    
    /**
     * Relasi ke model Pasien (Rekam medis milik satu pasien)
     */
    public function pasien()
    {
        return $this->belongsTo(Pasien::class, 'patient_id');
    }

    // ============ SCOPES ============
    
    /**
     * Filter rekam medis dari SIMRS
     */
    public function scopeDariSimrs($query)
    {
        return $query->where('source', 'SIMRS');
    }

    /**
     * Filter rekam medis dari input lokal
     */
    public function scopeLokal($query)
    {
        return $query->where('source', 'LOCAL');
    }

    /**
     * Sort rekam medis dari yang terbaru
     */
    public function scopeTerbaru($query)
    {
        return $query->orderBy('record_date', 'desc');
    }

    /**
     * Filter rekam medis berdasarkan tipe
     */
    public function scopeByTipe($query, $tipe)
    {
        return $query->where('record_type', $tipe);
    }

    // ============ HELPER METHODS ============
    
    /**
     * Cek apakah rekam medis dari SIMRS
     */
    public function isDariSimrs()
    {
        return $this->source === 'SIMRS';
    }

    /**
     * Cek apakah rekam medis dari input lokal
     */
    public function isLokal()
    {
        return $this->source === 'LOCAL';
    }
}