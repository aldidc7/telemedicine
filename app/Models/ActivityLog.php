<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * ============================================
 * MODEL LOGAKTIVITAS - AUDIT TRAIL AKTIVITAS
 * ============================================
 * 
 * Model ini merepresentasikan tabel 'activity_logs' di database.
 * Mencatat semua aktivitas penting di sistem untuk audit trail.
 * Bersifat immutable (tidak bisa diubah, hanya dibaca dan ditambah).
 * 
 * @property int $id - ID log
 * @property int $user_id - Siapa yang melakukan action
 * @property string $aksi - Apa yang dilakukan (create, update, delete, dll)
 * @property string $tipe_model - Jenis model yang diaffected
 * @property int $id_model - ID dari model yang diaffected
 * @property array $nilai_lama - Nilai lama sebelum update (JSON)
 * @property array $nilai_baru - Nilai baru setelah update (JSON)
 * @property string $deskripsi - Deskripsi detail activity
 * @property string $alamat_ip - IP address pengguna
 * @property string $user_agent - Browser/User Agent
 * @property \DateTime $created_at - Waktu activity terjadi
 * 
 * @author Aplikasi Telemedicine
 * @version 1.0
 */
class ActivityLog extends Model
{
    use HasFactory;

    /**
     * Nama tabel di database
     * 
     * @var string
     */
    protected $table = 'activity_logs';

    /**
     * Model ini bersifat immutable (tidak bisa diupdate)
     * Hanya punya kolom created_at, tidak ada updated_at
     * 
     * @var bool
     */
    const UPDATED_AT = null;

    /**
     * Kolom-kolom yang bisa di-assign secara mass (create/insert)
     * 
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'aksi',
        'tipe_model',
        'id_model',
        'nilai_lama',
        'nilai_baru',
        'deskripsi',
        'alamat_ip',
        'user_agent',
    ];

    /**
     * Casting tipe data untuk kolom tertentu
     * 
     * @var array<string, string>
     */
    protected $casts = [
        'nilai_lama' => 'array',
        'nilai_baru' => 'array',
        'created_at' => 'datetime',
    ];

    // ============ RELATIONSHIPS ============
    
    /**
     * Relasi ke model User (siapa yang melakukan activity)
     * 
     * Gunakan:
     * $log->pengguna  // Ambil data user yang melakukan action
     * $log->pengguna->name  // Nama user
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function pengguna()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ============ SCOPES ============
    
    /**
     * Sort log dari yang terbaru
     * 
     * Gunakan:
     * LogAktivitas::terbaru()->get()  // Ambil log terbaru duluan
     * LogAktivitas::terbaru()->paginate(20)  // Ambil 20 log terbaru
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeTerbaru($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Filter log berdasarkan user tertentu
     * 
     * Gunakan:
     * LogAktivitas::byUser(5)->get()  // Ambil log user ID 5
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $userId - ID user yang dicari
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Filter log berdasarkan jenis action
     * 
     * Gunakan:
     * LogAktivitas::byAksi('create')->get()  // Ambil semua create activity
     * LogAktivitas::byAksi('update')->count()  // Hitung update activity
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $aksi - Jenis action (create, update, delete, dll)
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByAksi($query, $aksi)
    {
        return $query->where('aksi', $aksi);
    }

    /**
     * Filter log berdasarkan tipe model
     * 
     * Gunakan:
     * LogAktivitas::byModel('Pasien')->get()  // Semua activity di model Pasien
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $tipeModel - Nama model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByModel($query, $tipeModel)
    {
        return $query->where('tipe_model', $tipeModel);
    }

    // ============ HELPER METHODS ============
    
    /**
     * Ambil nama user yang melakukan activity
     * 
     * Gunakan:
     * $nama = $log->getNamaPenggunaAttribute()
     * Atau langsung: $log->nama_pengguna
     * 
     * @return string
     */
    public function getNamaPenggunaAttribute()
    {
        return $this->pengguna->name ?? 'Sistem';
    }

    /**
     * Cek apakah ada perubahan data (ada nilai_lama dan nilai_baru)
     * 
     * Gunakan:
     * if ($log->adaPerubahan()) { ... }
     * 
     * @return bool
     */
    public function adaPerubahan()
    {
        return !empty($this->nilai_lama) && !empty($this->nilai_baru);
    }

    /**
     * Ambil daftar field yang berubah
     * 
     * Gunakan:
     * $fields = $log->getFieldYangBerubah()
     * // Returns: ['name' => ['Andi' => 'Budi'], 'email' => ['andi@mail' => 'budi@mail']]
     * 
     * @return array
     */
    public function getFieldYangBerubah()
    {
        if (!$this->adaPerubahan()) {
            return [];
        }

        $perubahan = [];
        foreach ($this->nilai_baru as $field => $nilaiBaruValue) {
            if (isset($this->nilai_lama[$field]) && $this->nilai_lama[$field] !== $nilaiBaruValue) {
                $perubahan[$field] = [
                    'dari' => $this->nilai_lama[$field],
                    'ke' => $nilaiBaruValue,
                ];
            }
        }

        return $perubahan;
    }

    /**
     * Ambil deskripsi readable dari activity
     * 
     * Gunakan:
     * $deskripsi = $log->getDeskripsiActivity()
     * // Returns: "Andi melakukan create pada model Pasien (ID: 5)"
     * 
     * @return string
     */
    public function getDeskripsiActivity()
    {
        $nama = $this->pengguna->name ?? 'Sistem';
        return "{$nama} melakukan {$this->aksi} pada model {$this->tipe_model} (ID: {$this->id_model})";
    }
}