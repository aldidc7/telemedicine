<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * ============================================
 * MODEL USER - BASE PENGGUNA SISTEM
 * ============================================
 * 
 * Model ini merepresentasikan tabel 'users' di database.
 * Menyimpan data login semua pengguna (Pasien, Dokter, Admin).
 * 
 * Struktur data:
 * - id: unique identifier (primary key)
 * - name: nama lengkap pengguna
 * - email: email untuk login (unique)
 * - password: password yang sudah di-hash (bcrypt)
 * - role: tipe pengguna (patient, doctor, admin)
 * - is_active: status aktif/tidak aktif akun
 * - last_login_at: kapan terakhir login
 * - created_at, updated_at: timestamp otomatis
 * 
 * @property int $id - ID unik pengguna
 * @property string $name - Nama lengkap
 * @property string $email - Email (unik)
 * @property string $password - Password (di-hash)
 * @property string $role - Role: 'pasien', 'dokter', 'admin'
 * @property bool $is_active - Status aktif (true/false)
 * @property \DateTime $last_login_at - Waktu login terakhir
 * @property-read \App\Models\Pasien|null $pasien - Relasi ke Pasien
 * @property-read \App\Models\Dokter|null $dokter - Relasi ke Dokter
 * @property-read \App\Models\Admin|null $admin - Relasi ke Admin
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Konsultasi[] $konsultasi - Relasi ke Konsultasi
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ActivityLog[] $activityLogs - Relasi ke ActivityLog
 * 
 * @author Aplikasi Telemedicine RSUD dr. R. Soedarsono
 * @version 1.0
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Kolom-kolom yang bisa di-assign secara mass (create/update)
     * Kolom ini aman untuk di-assign langsung dari input user
     * 
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'last_login_at',
        'email_verified_at',
        'email_verification_token',
        'password_reset_token',
        'password_reset_expires_at',
    ];

    /**
     * Kolom-kolom yang harus disembunyikan saat di-serialize ke JSON
     * Berguna untuk API response agar password tidak ketahuan
     * 
     * @var array<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'email_verification_token',
        'password_reset_token',
    ];

    /**
     * Casting tipe data untuk kolom tertentu
     * Otomatis convert database values ke tipe data yang benar
     * 
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',              // Tipe boolean
        'last_login_at' => 'datetime',         // Tipe datetime
        'email_verified_at' => 'datetime',     // Tipe datetime untuk email verification
        'password_reset_expires_at' => 'datetime',  // Tipe datetime untuk password reset expiry
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ============ RELATIONSHIPS (RELASI) ============
    
    /**
     * Relasi ke model Pasien (Satu user memiliki satu profile pasien)
     * Gunakan untuk pasien yang login/sign up
     * 
     * Gunakan:
     * $user->pasien  // Ambil data pasien
     * $user->pasien()->create([...])  // Create pasien baru
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function pasien()
    {
        return $this->hasOne(Pasien::class, 'user_id');
    }

    /**
     * Relasi ke model Dokter (Satu user memiliki satu profile dokter)
     * Gunakan untuk dokter yang login/sign up
     * 
     * Gunakan:
     * $user->dokter  // Ambil data dokter
     * $user->dokter->spesialisasi  // Spesialisasi dokter
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function dokter()
    {
        return $this->hasOne(Dokter::class, 'user_id');
    }

    /**
     * Relasi ke model Admin (Satu user memiliki satu profile admin)
     * Gunakan untuk admin yang login/sign up
     * 
     * Gunakan:
     * $user->admin  // Ambil data admin
     * $user->admin->tingkat_izin  // Permission level admin
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function admin()
    {
        return $this->hasOne(Admin::class, 'user_id');
    }

    /**
     * Relasi ke model PesanChat (Satu user bisa punya banyak pesan chat)
     * Tracking pesan yang dikirim user sebagai pengirim
     * 
     * Gunakan:
     * $user->pesanChat  // Ambil semua pesan dari user ini
     * $user->pesanChat()->count()  // Hitung total pesan
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function pesanChat()
    {
        return $this->hasMany(PesanChat::class, 'pengirim_id');
    }

    /**
     * Relasi ke model LogAktivitas (Tracking semua aktivitas user)
     * Setiap action user akan tercatat di activity_logs
     * 
     * Gunakan:
     * $user->logAktivitas  // Lihat semua aktivitas user
     * $user->logAktivitas()->count()  // Hitung total activity
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function logAktivitas()
    {
        return $this->hasMany(ActivityLog::class, 'user_id');
    }

    // ============ SCOPES (FILTER QUERY) ============
    
    /**
     * Scope untuk filter user yang aktif saja
     * 
     * Gunakan:
     * User::aktif()->get()  // Ambil semua user aktif
        User::aktif()->where('role', 'pasien')->get()  // Pasien aktif
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAktif($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk filter user yang tidak aktif
     * 
     * Gunakan:
     * User::tidakAktif()->get()  // Ambil semua user tidak aktif
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeTidakAktif($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope untuk filter user dengan role 'pasien'
     * 
     * Gunakan:
     * User::pasien()->get()  // Ambil semua pasien
     * User::pasien()->count()  // Hitung jumlah pasien
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePasien($query)
    {
        return $query->where('role', 'pasien');
    }

    /**
     * Scope untuk filter user dengan role 'dokter'
     * 
     * Gunakan:
     * User::dokter()->get()  // Ambil semua dokter
     * User::dokter()->count()  // Hitung jumlah dokter
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDokter($query)
    {
        return $query->where('role', 'dokter');
    }

    /**
     * Scope untuk filter user dengan role 'admin'
     * 
     * Gunakan:
     * User::admin()->get()  // Ambil semua admin
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAdmin($query)
    {
        return $query->where('role', 'admin');
    }

    // ============ HELPER METHODS ============
    
    /**
     * Cek apakah user adalah pasien
     * 
     * Gunakan:
     * if ($user->isPasien()) { ... }
     * 
     * @return bool true jika role = 'pasien'
     */
    public function isPasien()
    {
        return $this->role === 'pasien';
    }

    /**
     * Cek apakah user adalah dokter
     * 
     * Gunakan:
     * if ($user->isDokter()) { ... }
     * 
     * @return bool true jika role = 'dokter'
     */
    public function isDokter()
    {
        return $this->role === 'dokter';
    }

    /**
     * Cek apakah user adalah admin
     * 
     * Gunakan:
     * if ($user->isAdmin()) { ... }
     * 
     * @return bool true jika role = 'admin'
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Cek apakah user sedang aktif
     * 
     * Gunakan:
     * if ($user->isAktif()) { ... }
     * 
     * @return bool true jika is_active = true
     */
    public function isAktif()
    {
        return (bool) $this->is_active;
    }

    /**
     * Update waktu last login saat user login
     * 
     * Gunakan:
     * $user->updateLastLogin()  // Set last_login_at ke now()
     * 
     * @return void
     */
    public function updateLastLogin()
    {
        $this->update(['last_login_at' => now()]);
    }
}