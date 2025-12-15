<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * ============================================
 * MODEL PASIEN - DATA PROFIL PASIEN
 * ============================================
 * 
 * Model ini merepresentasikan tabel 'patients' di database.
 * Menyimpan data demografi dan informasi medis dasar pasien.
 * 
 * @property int $id - ID pasien
 * @property int $user_id - Foreign key ke users
 * @property string $nik - Nomor Identitas Kependudukan
 * @property \Date $tgl_lahir - Tanggal lahir
 * @property string $jenis_kelamin - Jenis kelamin
 * @property string $alamat - Alamat tinggal
 * @property string $no_telepon - Nomor telepon
 * @property string $nama_kontak_darurat - Nama kontak darurat
 * @property string $no_kontak_darurat - No. kontak darurat
 * @property string $golongan_darah - Golongan darah
 * 
 * @author Aplikasi Telemedicine RSUD dr. R. Soedarsono
 * @version 1.0
 */
class Pasien extends Model
{
    use HasFactory;

    /**
     * Nama tabel di database
     * 
     * @var string
     */
    protected $table = 'patients';

    /**
     * Kolom-kolom yang bisa di-assign secara mass (create/update)
     * 
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'nik',
        'date_of_birth',
        'place_of_birth',
        'gender',
        'marital_status',
        'religion',
        'ethnicity',
        'address',
        'phone_number',
        'emergency_contact_name',
        'emergency_contact_phone',
        'blood_type',
        'allergies',
    ];

    /**
     * Casting tipe data untuk kolom tertentu
     * 
     * @var array<string, string>
     */
    protected $casts = [
        'date_of_birth' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ============ RELATIONSHIPS (RELASI) ============
    
    /**
     * Relasi ke model User (Pasien punya satu user untuk login)
     * 
     * Gunakan:
     * $pasien->user  // Ambil data user pasien
     * $pasien->user->name  // Nama pasien
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi ke model Konsultasi (Pasien punya banyak konsultasi)
     * 
     * Gunakan:
     * $pasien->konsultasi  // Ambil semua konsultasi pasien
     * $pasien->konsultasi()->where('status', 'active')->get()
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function konsultasi()
    {
        return $this->hasMany(Konsultasi::class, 'patient_id');
    }

    /**
     * Relasi ke model RekamMedis (Pasien punya riwayat medis)
     * 
     * Gunakan:
     * $pasien->rekamMedis  // Ambil semua rekam medis pasien
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function rekamMedis()
    {
        return $this->hasMany(RekamMedis::class, 'patient_id');
    }

    // ============ HELPER METHODS ============
    
    /**
     * Hitung umur pasien dari tanggal lahir
     * 
     * Gunakan:
     * $umur = $pasien->umur  // Akses sebagai property
     * 
     * @return int|null umur pasien dalam tahun
     */
    public function getUmurAttribute()
    {
        return $this->date_of_birth?->diffInYears(now());
    }

    /**
     * Ambil konsultasi aktif pasien yang sedang berlangsung
     * 
     * Gunakan:
     * $konsultasi_aktif = $pasien->getKonsultasiAktif()
     * 
     * @return Konsultasi|null
     */
    public function getKonsultasiAktif()
    {
        return $this->konsultasi()
            ->where('status', 'active')
            ->latest()
            ->first();
    }

    /**
     * Ambil nama lengkap dari relasi pengguna
     * 
     * Gunakan:
     * $nama = $pasien->nama_lengkap  // Akses sebagai property
     * 
     * @return string nama lengkap pasien
     */
    public function getNamaLengkapAttribute()
    {
        return $this->user->name ?? 'Pasien';
    }
}