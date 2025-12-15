<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * ============================================
 * MODEL DOKTER - DATA PROFIL DOKTER
 * ============================================
 * 
 * Model ini merepresentasikan tabel 'doctors' di database.
 * Menyimpan data profil dan spesialisasi dokter.
 * 
 * Struktur data:
 * - id: unique identifier (primary key)
 * - user_id: foreign key ke tabel users
 * - spesialisasi: spesialisasi dokter (Dokter Anak, Umum, dll)
 * - no_lisensi: nomor lisensi (SIP/STR)
 * - no_telepon: nomor telepon
 * - tersedia: status ketersediaan dokter
 * - maks_konsultasi_simultan: batasan konsultasi simultan
 * 
 * Catatan:
 * - Untuk prototipe skripsi, semua dokter adalah Dokter Anak
 * - Status ketersediaan bisa diupdate via API
 * 
 * @property int $id - ID dokter
 * @property int $user_id - Foreign key ke users
 * @property string $specialization - Spesialisasi dokter
 * @property string $license_number - Nomor lisensi
 * @property bool $is_available - Status ketersediaan
 * @property int $max_concurrent_consultations - Batas konsultasi simultan
 * @property-read \App\Models\User $user - Relasi ke User
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Konsultasi[] $konsultasi - Relasi ke Konsultasi
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Rating[] $ratings - Relasi ke Rating
 * 
 * @author Aplikasi Telemedicine RSUD dr. R. Soedarsono
 * @version 1.0
 * @date 2024-01-10
 */
class Dokter extends Model
{
    use HasFactory;

    /**
     * Nama tabel di database
     * 
     * @var string
     */
    protected $table = 'doctors';

    /**
     * Kolom-kolom yang bisa di-assign secara mass (create/update)
     * Kolom ini aman untuk di-assign langsung dari input user
     * 
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'specialization',
        'license_number',
        'phone_number',
        'is_available',
        'is_verified',
        'max_concurrent_consultations',
        'verification_notes',
        'verified_at',
        'verified_by_admin_id',
        'profile_photo',
        'address',
        'place_of_birth',
        'birthplace_city',
        'marital_status',
        'gender',
        'blood_type',
        'ethnicity',
        'patient_synced',
    ];

    /**
     * Casting tipe data untuk kolom tertentu
     * Otomatis convert database values ke tipe data yang benar
     * 
     * @var array<string, string>
     */
    protected $casts = [
        'is_available' => 'boolean',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ============ RELATIONSHIPS (RELASI) ============
    
    /**
     * Relasi ke model User (Sebaliknya dari User::dokter())
     * Setiap dokter punya satu user untuk login
     * 
     * Gunakan:
     * $dokter->user  // Ambil data user dokter
     * $dokter->user->name  // Nama dokter
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi ke model Konsultasi (Dokter punya banyak konsultasi)
     * Satu dokter bisa menangani banyak konsultasi
     * 
     * Gunakan:
     * $dokter->konsultasi  // Ambil semua konsultasi dokter
     * $dokter->konsultasi()->where('status', 'active')->get()
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function konsultasi()
    {
        return $this->hasMany('App\Models\Konsultasi', 'doctor_id');
    }

    /**
     * Relasi ke model Rating (Dokter punya banyak ratings dari pasien)
     * Digunakan untuk menampilkan rating dokter
     * 
     * Gunakan:
     * $dokter->ratings  // Ambil semua rating dokter
     * $dokter->ratings()->avg('rating')  // Average rating
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function ratings()
    {
        return $this->hasMany(Rating::class, 'dokter_id');
    }

    // ============ SCOPES (FILTER QUERY) ============
    
    /**
     * Filter dokter yang tersedia
     * 
     * Gunakan:
     * Dokter::tersedia()->get()  // Ambil dokter yang tersedia
     * Dokter::tersedia()->count()  // Hitung dokter tersedia
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeTersedia($query)
    {
        return $query->where('is_available', true);
    }

    /**
     * Filter dokter berdasarkan status verifikasi admin
     * Hanya dokter yang sudah diverifikasi bisa tampil di list dan terima konsultasi
     * 
     * Gunakan:
     * Dokter::terverifikasi()->get()  // Semua dokter terverifikasi
     * Dokter::terverifikasi()->count()  // Hitung dokter terverifikasi
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeTerverifikasi($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Filter dokter yang belum diverifikasi (pending)
     * Digunakan untuk admin melihat dokter yang perlu di-approve
     * 
     * Gunakan:
     * Dokter::pending()->get()  // Dokter menunggu verifikasi
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where('is_verified', false);
    }

    /**
     * Filter dokter berdasarkan spesialisasi
     * 
     * Gunakan:
     * Dokter::bySpesialisasi('Dokter Anak')->get()  // Dokter anak saja
     * Dokter::bySpesialisasi('Dokter Umum')->count()  // Dokter umum
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $spesialisasi - Spesialisasi yang dicari
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBySpesialisasi($query, $spesialisasi)
    {
        return $query->where('specialization', $spesialisasi);
    }

    // ============ HELPER METHODS ============
    
    /**
     * Hitung jumlah konsultasi aktif dokter saat ini
     * Digunakan untuk mengecek apakah dokter bisa menerima konsultasi baru
     * 
     * Gunakan:
     * $jml = $dokter->getJmlKonsultasiAktif()
     * if ($jml < 5) { ... } // Masih bisa terima konsultasi
     * 
     * @return int jumlah konsultasi aktif
     */
    public function getJmlKonsultasiAktif()
    {
        // Query konsultasi dengan status 'active'
        return $this->konsultasi()
            ->where('status', 'active')
            ->count();
    }

    /**
     * Cek apakah dokter bisa menerima konsultasi baru
     * 
     * Kondisi (harus SEMUA terpenuhi):
     * 1. Dokter harus tersedia (tersedia = true)
     * 2. Jumlah konsultasi aktif < max_concurrent_consultations
     * 
     * Gunakan:
     * if ($dokter->bisaTerimaKonsultasi()) {
     *     // Assign konsultasi ke dokter ini
     * } else {
     *     // Cari dokter lain
     * }
     * 
     * @return bool true jika dokter bisa terima konsultasi baru
     */
    public function bisaTerimaKonsultasi()
    {
        // Cek 2 kondisi: tersedia AND jumlah konsultasi < max
        return $this->is_available && 
               $this->getJmlKonsultasiAktif() < $this->max_concurrent_consultations;
    }

    /**
     * Ambil nama lengkap dokter dari relasi pengguna
     * 
     * Gunakan:
     * $nama = $dokter->getNamaLengkapAttribute()
     * Atau bisa langsung: $dokter->nama_lengkap
     * 
     * @return string nama dokter
     */
    public function getNamaLengkapAttribute()
    {
        return $this->user->name;
    }

    /**
     * Cek apakah dokter tersedia
     * 
     * @return bool true jika tersedia
     */
    public function isTersedia()
    {
        return (bool) $this->is_available;
    }

    /**
     * Cek apakah dokter tidak tersedia
     * 
     * @return bool true jika tidak tersedia
     */
    public function isTidakTersedia()
    {
        return !$this->is_available;
    }
}