<?php

namespace App\Repositories;

use App\Models\Konsultasi;
use Illuminate\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Konsultasi Repository
 * 
 * Menangani semua database queries untuk Konsultasi model
 * dengan optimization dan eager loading
 */
class KonsultasiRepository
{
    /**
     * Get all consultations with proper eager loading
     * FIXES N+1: Eager load pasien, dokter, chat messages
     */
    public function getAllWithRelations($perPage = 15)
    {
        return Konsultasi::with([
            'pasien:id,user_id,no_identitas,nama_lengkap,tanggal_lahir,no_telepon',
            'pasien.user:id,name,email',
            'dokter:id,user_id,license_number,specialization',
            'dokter.user:id,name,email',
            'pesanChat' => function ($query) {
                $query->latest()->limit(1);
            },
            'rating'
        ])
        ->latest('updated_at')
        ->paginate($perPage);
    }

    /**
     * Get consultation with all relations for detail view
     */
    public function getWithAllRelations($id)
    {
        return Konsultasi::with([
            'pasien:id,user_id,no_identitas,nama_lengkap,tanggal_lahir,no_telepon,alamat,jenis_kelamin,golongan_darah,alergi',
            'pasien.user:id,name,email,phone',
            'dokter:id,user_id,license_number,specialization',
            'dokter.user:id,name,email,phone',
            'pesanChat' => function ($query) {
                $query->with('pengirim:id,name')->latest();
            },
            'rating:id,konsultasi_id,rating,komentar,created_at',
            'rekamMedis:id,konsultasi_id,dokter_id,diagnosis,resep,created_at'
        ])
        ->findOrFail($id);
    }

    /**
     * Get consultations by patient with eager loading
     */
    public function getByPasienId($pasienId, $perPage = 15)
    {
        return Konsultasi::with([
            'dokter:id,user_id,specialization',
            'dokter.user:id,name',
            'pesanChat' => function ($query) {
                $query->where('dibaca', false)->count();
            }
        ])
        ->where('pasien_id', $pasienId)
        ->latest('created_at')
        ->paginate($perPage);
    }

    /**
     * Get consultations by doctor with eager loading
     */
    public function getByDokterId($dokterId, $perPage = 15)
    {
        return Konsultasi::with([
            'pasien:id,user_id,nama_lengkap,no_telepon',
            'pasien.user:id,name',
            'pesanChat' => function ($query) {
                $query->where('dibaca', false);
            }
        ])
        ->where('dokter_id', $dokterId)
        ->latest('created_at')
        ->paginate($perPage);
    }

    /**
     * Get recent consultations for dashboard
     */
    public function getRecentForDashboard($limit = 5)
    {
        return Konsultasi::with([
            'pasien:id,nama_lengkap',
            'dokter:id,spesialisasi',
            'dokter.user:id,name'
        ])
        ->latest('created_at')
        ->limit($limit)
        ->get();
    }

    /**
     * Count active consultations
     */
    public function countActive()
    {
        return Konsultasi::where('status', 'aktif')->count();
    }

    /**
     * Get consultation statistics
     */
    public function getStatistics()
    {
        return Konsultasi::selectRaw('
            COUNT(*) as total,
            COUNT(CASE WHEN status = "aktif" THEN 1 END) as active,
            COUNT(CASE WHEN status = "selesai" THEN 1 END) as completed,
            COUNT(CASE WHEN status = "dibatalkan" THEN 1 END) as cancelled
        ')->first();
    }
}
