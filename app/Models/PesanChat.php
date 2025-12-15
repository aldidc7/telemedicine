<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * ============================================
 * MODEL PESANCHAT - DATA PESAN CHAT
 * ============================================
 * 
 * Model ini merepresentasikan tabel 'chat_messages' di database.
 * Menyimpan semua pesan chat dalam konsultasi.
 * 
 * @property int $id - ID pesan
 * @property int $konsultasi_id - Foreign key ke consultations
 * @property int $pengirim_id - Foreign key ke users
 * @property string $pesan - Isi pesan
 * @property string $tipe_pesan - Tipe pesan
 * @property \DateTime|null $dibaca_pada - Waktu dibaca
 * 
 * @author Aplikasi Telemedicine RSUD dr. R. Soedarsono
 * @version 1.0
 */
class PesanChat extends Model
{
    use HasFactory;

    /**
     * Nama tabel di database
     * 
     * @var string
     */
    protected $table = 'chat_messages';

    /**
     * Kolom-kolom yang bisa di-assign secara mass (create/update)
     * 
     * @var array<string>
     */
    protected $fillable = [
        'consultation_id',
        'sender_id',
        'message',
        'message_type',
        'file_url',
        'read_at',
    ];

    /**
     * Casting tipe data untuk kolom tertentu
     * 
     * @var array<string, string>
     */
    protected $casts = [
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ============ RELATIONSHIPS (RELASI) ============
    
    /**
     * Relasi ke model Konsultasi (Pesan milik satu konsultasi)
     * 
     * Gunakan:
     * $pesan->konsultasi  // Ambil data konsultasi
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function konsultasi()
    {
        return $this->belongsTo(Konsultasi::class, 'consultation_id');
    }

    /**
     * Relasi ke model User (yang mengirim pesan)
     * 
     * Gunakan:
     * $pesan->pengirim  // Ambil data user pengirim
     * $pesan->pengirim->name  // Nama pengirim
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function pengirim()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    // ============ SCOPES (FILTER QUERY) ============
    
    /**
     * Filter pesan yang belum dibaca
     * 
     * Gunakan:
     * PesanChat::belumDibaca()->get()  // Ambil semua pesan belum dibaca
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBelumDibaca($query)
    {
        return $query->whereNull('dibaca_pada');
    }

    /**
     * Filter pesan yang sudah dibaca
     * 
     * Gunakan:
     * PesanChat::sudahDibaca()->get()  // Ambil semua pesan sudah dibaca
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSudahDibaca($query)
    {
        return $query->whereNotNull('dibaca_pada');
    }

    // ============ HELPER METHODS ============
    
    /**
     * Cek apakah pesan sudah dibaca
     * 
     * Gunakan:
     * if ($pesan->isSudahDibaca()) { ... }
     * 
     * @return bool true jika sudah dibaca
     */
    public function isSudahDibaca()
    {
        return $this->dibaca_pada !== null;
    }

    /**
     * Mark pesan sebagai sudah dibaca
     * 
     * Gunakan:
     * $pesan->markAsDibaca()  // Set dibaca_pada ke now()
     * 
     * @return void
     */
    public function markAsDibaca()
    {
        if (!$this->isSudahDibaca()) {
            $this->update(['dibaca_pada' => now()]);
        }
    }

    /**
     * Ambil nama pengirim pesan
     * 
     * Gunakan:
     * $nama = $pesan->nama_pengirim  // Akses sebagai property
     * 
     * @return string nama pengirim
     */
    public function getNamaPengirimAttribute()
    {
        return $this->pengirim->name ?? 'Pengguna';
    }
}