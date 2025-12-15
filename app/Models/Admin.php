<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * ============================================
 * MODEL ADMIN - DATA PROFIL ADMIN SISTEM
 * ============================================
 * 
 * Model ini merepresentasikan tabel 'admins' di database.
 * Menyimpan data profil dan permission level admin.
 * 
 * Permission Level:
 * - 1 (dasar): Hanya lihat dashboard
 * - 2 (lanjutan): Lihat dashboard, kelola user, lihat logs
 * - 3 (super): Full access ke semua fitur
 * 
 * @property int $id - ID admin
 * @property int $user_id - Foreign key ke users
 * @property int $tingkat_izin - Level permission
 * @property string $catatan - Catatan tambahan
 * 
 * @author Aplikasi Telemedicine
 * @version 1.0
 */
class Admin extends Model
{
    use HasFactory;

    /**
     * Nama tabel di database
     * 
     * @var string
     */
    protected $table = 'admins';

    /**
     * Kolom-kolom yang bisa di-assign secara mass (create/update)
     * 
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'permission_level',
        'notes',
    ];

    /**
     * Casting tipe data untuk kolom tertentu
     * 
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ============ RELATIONSHIPS ============
    
    /**
     * Relasi ke model User (Admin punya satu user untuk login)
     * 
     * Gunakan:
     * $admin->user  // Ambil data user admin
     * $admin->user->name  // Nama admin
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ============ HELPER METHODS ============
    
    /**
     * Cek apakah admin punya permission dasar (level 1)
     * 
     * @return bool
     */
    public function isDasar()
    {
        return $this->permission_level === 1;
    }

    /**
     * Cek apakah admin punya permission lanjutan (level 2)
     * 
     * @return bool
     */
    public function isLanjutan()
    {
        return $this->permission_level === 2;
    }

    /**
     * Cek apakah admin punya permission super (level 3)
     * 
     * @return bool
     */
    public function isSuper()
    {
        return $this->permission_level === 3;
    }

    /**
     * Cek apakah admin bisa edit user
     * Admin dengan level 2 ke atas bisa edit user
     * 
     * @return bool
     */
    public function bisaEditUser()
    {
        return $this->permission_level >= 2;
    }

    /**
     * Ambil nama admin dari relasi pengguna
     * 
     * Gunakan:
     * $admin->getNamaAttribute() atau langsung: $admin->nama
     * 
     * @return string
     */
    public function getNamaAttribute()
    {
        return $this->user->name;
    }

    /**
     * Ambil permission level sebagai text
     * 
     * Gunakan:
     * $admin->getTingkatIzinTextAttribute() atau: $admin->tingkat_izin_text
     * 
     * @return string
     */
    public function getTingkatIzinTextAttribute()
    {
        return match($this->permission_level) {
            1 => 'Dasar',
            2 => 'Lanjutan',
            3 => 'Super Admin',
            default => 'Tidak Diketahui'
        };
    }
}