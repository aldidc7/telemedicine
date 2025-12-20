<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * ============================================
 * MODEL KONSULTASI - DATA KONSULTASI
 * ============================================
 * 
 * Model ini merepresentasikan tabel 'consultations' di database.
 * Menyimpan data setiap konsultasi antara pasien dan dokter.
 * 
 * Status Konsultasi:
 * - menunggu: Menunggu dokter merespons
 * - aktif: Dokter sedang melayani konsultasi
 * - selesai: Konsultasi selesai
 * - dibatalkan: Konsultasi dibatalkan
 * 
 * @property int $id - ID konsultasi
 * @property int $pasien_id - Foreign key ke patients
 * @property int|null $dokter_id - Foreign key ke doctors
 * @property string $jenis_keluhan - Jenis keluhan
 * @property string $deskripsi - Deskripsi keluhan
 * @property string $status - Status konsultasi
 * @property Carbon|null $waktu_mulai - Waktu mulai
 * @property Carbon|null $waktu_selesai - Waktu selesai
 * @property Carbon|null $waktu_sinkronisasi - Waktu sinkronisasi dengan SIMRS
 * @property string|null $catatan_penutup - Catatan penutup
 * @property bool $sudah_sinkron_simrs - Sudah sync ke SIMRS?
 * @property Carbon $created_at - Waktu dibuat
 * @property Carbon $updated_at - Waktu diupdate
 * 
 * @author Aplikasi Telemedicine
 * @version 1.0
 */
class Konsultasi extends Model
{
    use HasFactory;

    protected $table = 'consultations';

    // Typed properties for better IDE support
    public ?Carbon $start_time = null;
    public ?Carbon $end_time = null;
    public ?Carbon $synced_at = null;

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'complaint_type',
        'description',
        'status',
        'start_time',
        'end_time',
        'closing_notes',
        'simrs_synced',
        'synced_at',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'synced_at' => 'datetime',
        'simrs_synced' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ============ RELATIONSHIPS ============

    /**
     * Relasi ke model Pasien (Konsultasi milik satu pasien)
     */
    public function pasien()
    {
        return $this->belongsTo(Pasien::class, 'patient_id');
    }

    /**
     * Reminders untuk konsultasi ini
     */
    public function reminders()
    {
        return $this->hasMany(AppointmentReminder::class, 'appointment_id');
    }

    /**
     * Relasi ke model Dokter (Konsultasi ditangani satu dokter)
     */
    public function dokter()
    {
        return $this->belongsTo(Dokter::class, 'doctor_id');
    }

    /**
     * Relasi ke model PesanChat (Konsultasi punya banyak pesan chat)
     */
    public function pesanChat()
    {
        return $this->hasMany(PesanChat::class, 'consultation_id')
            ->orderBy('created_at', 'asc');
    }

    /**
     * Relasi ke model ConsultationMessage (In-call chat messages)
     */
    public function messages()
    {
        return $this->hasMany(ConsultationMessage::class, 'consultation_id')
            ->orderBy('created_at', 'asc');
    }

    /**
     * Relasi ke model Rating (Konsultasi punya satu rating dari pasien)
     */
    public function rating()
    {
        return $this->hasOne(Rating::class, 'konsultasi_id');
    }

    // ============ SCOPES ============

    /**
     * Filter konsultasi dengan status 'pending'
     */
    public function scopeMenunggu($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Filter konsultasi dengan status 'active'
     */
    public function scopeAktif($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Filter konsultasi dengan status 'closed'
     */
    public function scopeSelesai($query)
    {
        return $query->where('status', 'closed');
    }

    /**
     * Sort konsultasi dari yang terbaru
     */
    public function scopeTerbaru($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Filter konsultasi yang sudah sinkronisasi ke SIMRS
     */
    public function scopeSudahSinkron($query)
    {
        return $query->where('simrs_synced', true);
    }

    // ============ HELPER METHODS ============

    /**
     * Cek apakah konsultasi sedang menunggu
     */
    public function isMenunggu()
    {
        return $this->status === 'pending';
    }

    /**
     * Cek apakah konsultasi sedang aktif
     */
    public function isAktif()
    {
        return $this->status === 'active';
    }

    /**
     * Cek apakah konsultasi sudah selesai
     */
    public function isSelesai()
    {
        return $this->status === 'closed';
    }

    /**
     * Hitung durasi konsultasi dalam menit
     * 
     * @return int|null
     */
    public function getDurasiAttribute(): ?int
    {
        if ($this->start_time instanceof Carbon && $this->end_time instanceof Carbon) {
            return $this->end_time->diffInMinutes($this->start_time);
        }
        return null;
    }

    /**
     * Cek apakah konsultasi sudah sinkronisasi ke SIMRS
     */
    public function isSudahSinkron()
    {
        return (bool) $this->simrs_synced;
    }

    /**
     * Ambil nama pasien
     */
    public function getNamaPasienAttribute()
    {
        return $this->pasien->user->name;
    }

    /**
     * Ambil nama dokter
     */
    public function getNamaDokterAttribute()
    {
        return $this->dokter?->user->name ?? 'Belum Ditugaskan';
    }
}
