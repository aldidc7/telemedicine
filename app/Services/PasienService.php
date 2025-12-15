<?php

namespace App\Services;

use App\Models\Pasien;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Service untuk mengelola data Pasien
 */
class PasienService
{
    /**
     * Get list of all pasien with pagination and filters
     * 
     * @param array $filters
     * @return mixed
     */
    public function getAllPasien(array $filters = [])
    {
        $perPage = $filters['per_page'] ?? 15;
        $search = $filters['search'] ?? null;
        $sort = $filters['sort'] ?? 'created_at';
        $order = $filters['order'] ?? 'desc';

        $query = Pasien::with('user', 'konsultasi', 'rekamMedis');

        // Search filter
        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })->orWhere('nik', 'like', "%{$search}%");
        }

        // Sort
        $query->orderBy($sort, $order);

        // Paginate
        return $query->paginate($perPage);
    }

    /**
     * Get pasien by ID
     * 
     * @param int $id
     * @return Pasien|null
     */
    public function getPasienById(int $id)
    {
        return Pasien::with('user', 'konsultasi', 'rekamMedis')->find($id);
    }

    /**
     * Create new pasien
     * 
     * @param array $data
     * @return Pasien
     */
    public function createPasien(array $data)
    {
        return DB::transaction(function () use ($data) {
            // Create user
            $user = User::create([
                'name' => $data['nama'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => 'pasien',
                'is_active' => true,
            ]);

            // Create pasien record
            $pasien = Pasien::create([
                'user_id' => $user->id,
                'nik' => $data['nik'],
                'tgl_lahir' => $data['tgl_lahir'],
                'jenis_kelamin' => $data['jenis_kelamin'],
                'alamat' => $data['alamat'],
                'no_telepon' => $data['no_telepon'],
                'golongan_darah' => $data['golongan_darah'] ?? null,
                'alergi' => $data['alergi'] ?? null,
                'nama_kontak_darurat' => $data['nama_kontak_darurat'] ?? null,
                'no_kontak_darurat' => $data['no_kontak_darurat'] ?? null,
            ]);

            return $pasien->load('pengguna');
        });
    }

    /**
     * Update pasien data
     * 
     * @param Pasien $pasien
     * @param array $data
     * @return Pasien
     */
    public function updatePasien(Pasien $pasien, array $data)
    {
        return DB::transaction(function () use ($pasien, $data) {
            // Update user data if provided
            if (isset($data['nama']) || isset($data['email'])) {
                $pasien->pengguna->update([
                    'name' => $data['nama'] ?? $pasien->pengguna->name,
                    'email' => $data['email'] ?? $pasien->pengguna->email,
                ]);
            }

            // Update pasien data
            $pasienData = [];
            $fields = ['tgl_lahir', 'jenis_kelamin', 'alamat', 'no_telepon', 'golongan_darah', 'alergi', 'nama_kontak_darurat', 'no_kontak_darurat'];
            
            foreach ($fields as $field) {
                if (isset($data[$field])) {
                    $pasienData[$field] = $data[$field];
                }
            }

            if (!empty($pasienData)) {
                $pasien->update($pasienData);
            }

            return $pasien->fresh(['pengguna']);
        });
    }

    /**
     * Delete pasien
     * 
     * @param Pasien $pasien
     * @return bool
     */
    public function deletePasien(Pasien $pasien)
    {
        return DB::transaction(function () use ($pasien) {
            $userId = $pasien->user_id;
            $pasien->delete();
            User::find($userId)->delete();
            return true;
        });
    }

    /**
     * Get pasien medical records
     * 
     * @param Pasien $pasien
     * @param array $filters
     * @return mixed
     */
    public function getPasienMedicalRecords(Pasien $pasien, array $filters = [])
    {
        $perPage = $filters['per_page'] ?? 10;
        $sort = $filters['sort'] ?? 'created_at';
        $order = $filters['order'] ?? 'desc';

        return $pasien->rekamMedis()
            ->orderBy($sort, $order)
            ->paginate($perPage);
    }

    /**
     * Get pasien consultations
     * 
     * @param Pasien $pasien
     * @param array $filters
     * @return mixed
     */
    public function getPasienConsultations(Pasien $pasien, array $filters = [])
    {
        $perPage = $filters['per_page'] ?? 10;
        $status = $filters['status'] ?? null;
        $sort = $filters['sort'] ?? 'created_at';
        $order = $filters['order'] ?? 'desc';

        $query = $pasien->konsultasi();

        if ($status) {
            $query->where('status', $status);
        }

        return $query->orderBy($sort, $order)->paginate($perPage);
    }
}
