<?php

namespace App\Services;

use App\Models\Konsultasi;
use App\Models\User;
use Illuminate\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Consultation Service
 * 
 * Handle semua logika bisnis untuk konsultasi
 */
class ConsultationService
{
    /**
     * Get semua konsultasi dengan filter dan pagination
     *
     * @param array $filters
     * @param int $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getAllConsultations(array $filters = [], int $perPage = 15)
    {
        $query = Konsultasi::with(['pasien', 'dokter', 'chats']);

        // Filter by status
        if (isset($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        // Filter by doctor
        if (isset($filters['dokter_id'])) {
            $query->where('dokter_id', $filters['dokter_id']);
        }

        // Filter by patient
        if (isset($filters['pasien_id'])) {
            $query->where('pasien_id', $filters['pasien_id']);
        }

        // Filter by service type
        if (isset($filters['tipe_layanan'])) {
            $query->where('tipe_layanan', $filters['tipe_layanan']);
        }

        // Search by keluhan
        if (isset($filters['search'])) {
            $query->where('keluhan', 'like', '%' . $filters['search'] . '%');
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Get konsultasi by ID with relations
     *
     * @param int $id
     * @return Konsultasi|null
     */
    public function getConsultationById(int $id): ?Konsultasi
    {
        return Konsultasi::with(['pasien', 'dokter', 'chats', 'rekamMedis'])
            ->find($id);
    }

    /**
     * Create konsultasi baru
     *
     * @param User $pasien
     * @param array $data
     * @return Konsultasi
     */
    public function createConsultation(User $pasien, array $data): Konsultasi
    {
        $konsultasi = Konsultasi::create([
            'pasien_id' => $pasien->id,
            'dokter_id' => $data['dokter_id'],
            'keluhan' => $data['keluhan'],
            'tipe_layanan' => $data['tipe_layanan'],
            'tanggal_konsultasi' => $data['tanggal_konsultasi'] ?? null,
            'waktu_mulai' => $data['waktu_mulai'] ?? null,
            'status' => 'pending',
        ]);

        return $konsultasi->fresh();
    }

    /**
     * Update konsultasi
     *
     * @param Konsultasi $konsultasi
     * @param array $data
     * @return Konsultasi
     */
    public function updateConsultation(Konsultasi $konsultasi, array $data): Konsultasi
    {
        $updateData = [];

        if (isset($data['status'])) {
            $updateData['status'] = $data['status'];
        }

        if (isset($data['catatan_dokter'])) {
            $updateData['catatan_dokter'] = $data['catatan_dokter'];
        }

        if (isset($data['diagnosis'])) {
            $updateData['diagnosis'] = $data['diagnosis'];
        }

        if (isset($data['tanggal_konsultasi'])) {
            $updateData['tanggal_konsultasi'] = $data['tanggal_konsultasi'];
        }

        if (isset($data['waktu_mulai'])) {
            $updateData['waktu_mulai'] = $data['waktu_mulai'];
        }

        $konsultasi->update($updateData);

        return $konsultasi->fresh();
    }

    /**
     * Cancel konsultasi
     *
     * @param Konsultasi $konsultasi
     * @param string|null $alasan
     * @return Konsultasi
     */
    public function cancelConsultation(Konsultasi $konsultasi, ?string $alasan = null): Konsultasi
    {
        $konsultasi->update([
            'status' => 'cancelled',
            'alasan_pembatalan' => $alasan,
        ]);

        return $konsultasi->fresh();
    }

    /**
     * Complete konsultasi
     *
     * @param Konsultasi $konsultasi
     * @param array $data
     * @return Konsultasi
     */
    public function completeConsultation(Konsultasi $konsultasi, array $data): Konsultasi
    {
        $konsultasi->update([
            'status' => 'completed',
            'diagnosis' => $data['diagnosis'] ?? null,
            'catatan_dokter' => $data['catatan_dokter'] ?? null,
        ]);

        return $konsultasi->fresh();
    }

    /**
     * Get konsultasi untuk pasien tertentu
     *
     * @param User $pasien
     * @param int $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getPatientConsultations(User $pasien, int $perPage = 15)
    {
        return Konsultasi::where('pasien_id', $pasien->id)
            ->with(['pasien', 'dokter', 'chats'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get konsultasi untuk dokter tertentu
     *
     * @param User $dokter
     * @param int $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getDoctorConsultations(User $dokter, int $perPage = 15)
    {
        return Konsultasi::where('dokter_id', $dokter->id)
            ->with(['pasien', 'dokter', 'chats'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get consultation stats
     *
     * @return array
     */
    public function getConsultationStats(): array
    {
        return [
            'total' => Konsultasi::count(),
            'pending' => Konsultasi::where('status', 'pending')->count(),
            'active' => Konsultasi::where('status', 'active')->count(),
            'completed' => Konsultasi::where('status', 'completed')->count(),
            'cancelled' => Konsultasi::where('status', 'cancelled')->count(),
        ];
    }
}
