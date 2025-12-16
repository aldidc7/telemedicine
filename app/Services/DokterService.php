<?php

namespace App\Services;

use App\Models\Dokter;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Service untuk mengelola data Dokter
 */
class DokterService
{
    /**
     * Get list of all dokter with pagination and filters
     * 
     * @param array $filters
     * @return mixed
     */
    public function getAllDokter(array $filters = [])
    {
        $perPage = $filters['per_page'] ?? 15;
        $search = $filters['search'] ?? null;
        $spesialisasi = $filters['spesialisasi'] ?? null;
        $status = $filters['status'] ?? null;
        $isVerified = $filters['is_verified'] ?? null;
        $tersedia = $filters['tersedia'] ?? null;
        $sort = $filters['sort'] ?? 'created_at';
        $order = $filters['order'] ?? 'desc';

        $query = Dokter::with('user', 'konsultasi');

        // Search filter
        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })->orWhere('nip', 'like', "%{$search}%");
        }

        // Filter by specialization
        if ($spesialisasi) {
            $query->where('specialization', $spesialisasi);
        }

        // Filter by status
        if ($status) {
            $query->whereHas('user', function ($q) use ($status) {
                $q->where('is_active', $status === 'aktif');
            });
        }

        // Filter by verification status
        if ($isVerified !== null) {
            $query->where('is_verified', $isVerified);
        }

        // Filter by availability
        if ($tersedia !== null) {
            $query->where('tersedia', $tersedia);
        }

        // Sort
        $query->orderBy($sort, $order);

        // Paginate
        return $query->paginate($perPage);
    }

    /**
     * Get dokter by ID
     * 
     * @param int $id
     * @return Dokter|null
     */
    public function getDokterById(int $id)
    {
        return Dokter::with('user', 'konsultasi')->find($id);
    }

    /**
     * Get dokter by user ID
     * 
     * @param int $userId
     * @return Dokter|null
     */
    public function getDokterByUserId(int $userId)
    {
        return Dokter::where('user_id', $userId)->with('user', 'konsultasi')->first();
    }

    /**
     * Create new dokter
     * 
     * @param array $data
     * @return Dokter
     */
    public function createDokter(array $data)
    {
        return DB::transaction(function () use ($data) {
            // Create user
            $user = User::create([
                'name' => $data['nama'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => 'dokter',
                'is_active' => true,
            ]);

            // Create dokter record
            $dokter = Dokter::create([
                'user_id' => $user->id,
                'license_number' => $data['nip'],
                'specialization' => $data['spesialisasi'],
                'address' => $data['alamat'] ?? null,
                'phone_number' => $data['no_telepon'] ?? null,
            ]);

            return $dokter->load('pengguna');
        });
    }

    /**
     * Update dokter data
     * 
     * @param Dokter $dokter
     * @param array $data
     * @return Dokter
     */
    public function updateDokter(Dokter $dokter, array $data)
    {
        return DB::transaction(function () use ($dokter, $data) {
            // Update user data if provided
            if (isset($data['nama']) || isset($data['email'])) {
                $dokter->pengguna->update([
                    'name' => $data['nama'] ?? $dokter->pengguna->name,
                    'email' => $data['email'] ?? $dokter->pengguna->email,
                ]);
            }

            // Update dokter data
            $dokterData = [];
            $fields = ['specialization', 'address', 'phone_number'];
            
            foreach ($fields as $field) {
                if (isset($data[$field])) {
                    $dokterData[$field] = $data[$field];
                }
            }

            if (!empty($dokterData)) {
                $dokter->update($dokterData);
            }

            return $dokter->fresh(['pengguna']);
        });
    }

    /**
     * Delete dokter
     * 
     * @param Dokter $dokter
     * @return bool
     */
    public function deleteDokter(Dokter $dokter)
    {
        return DB::transaction(function () use ($dokter) {
            $userId = $dokter->user_id;
            $dokter->delete();
            User::find($userId)->delete();
            return true;
        });
    }

    /**
     * Update dokter availability
     * 
     * @param Dokter $dokter
     * @param array $data
     * @return Dokter
     */
    public function updateKetersediaan(Dokter $dokter, array $data)
    {
        return $this->updateDokter($dokter, [
            'jam_praktik' => $data['jam_praktik'] ?? $dokter->jam_praktik,
            'hari_praktik' => $data['hari_praktik'] ?? $dokter->hari_praktik,
        ]);
    }
}
