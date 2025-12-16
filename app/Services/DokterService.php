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
            if (isset($data['name']) || isset($data['email'])) {
                $dokter->user->update([
                    'name' => $data['name'] ?? $dokter->user->name,
                    'email' => $data['email'] ?? $dokter->user->email,
                ]);
            }

            // Handle profile photo upload
            if (isset($data['profile_photo'])) {
                if ($data['profile_photo'] instanceof \Illuminate\Http\UploadedFile) {
                    try {
                        // Delete old photo if exists
                        if ($dokter->profile_photo) {
                            $oldPath = str_replace('/storage/', '', $dokter->profile_photo);
                            $fullPath = storage_path('app/public/' . $oldPath);
                            if (file_exists($fullPath)) {
                                @unlink($fullPath); // Suppress warnings if file doesn't exist
                            }
                        }
                        
                        // Store new photo
                        $path = $data['profile_photo']->store('dokter-profiles', 'public');
                        if (!$path) {
                            throw new \Exception('Failed to store profile photo');
                        }
                        $data['profile_photo'] = '/storage/' . $path;
                    } catch (\Exception $e) {
                        \Log::error('Profile photo upload failed: ' . $e->getMessage());
                        throw $e;
                    }
                }
            }

            // Update dokter data - all fields except user_id and timestamps
            $dokterFields = [
                'specialization', 'address', 'phone_number', 'license_number',
                'gender', 'birthplace_city', 'place_of_birth', 'blood_type',
                'marital_status', 'ethnicity', 'max_concurrent_consultations',
                'is_available', 'tersedia', 'profile_photo'
            ];
            
            $dokterData = [];
            foreach ($dokterFields as $field) {
                if (isset($data[$field])) {
                    $dokterData[$field] = $data[$field];
                }
            }

            // Handle is_available and tersedia as synonyms
            if (isset($data['is_available']) && !isset($data['tersedia'])) {
                $dokterData['tersedia'] = $data['is_available'];
                $dokterData['is_available'] = $data['is_available'];
            } elseif (isset($data['tersedia']) && !isset($data['is_available'])) {
                $dokterData['is_available'] = $data['tersedia'];
                $dokterData['tersedia'] = $data['tersedia'];
            }

            if (!empty($dokterData)) {
                $dokter->update($dokterData);
            }

            return $dokter->fresh(['user']);
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
        $updateData = [];
        
        // Handle 'tersedia' or 'is_available' field
        if (isset($data['tersedia']) || isset($data['is_available'])) {
            $isAvailable = $data['tersedia'] ?? $data['is_available'] ?? false;
            $updateData['is_available'] = $isAvailable;
        }
        
        // Handle jam_praktik if provided
        if (isset($data['jam_praktik'])) {
            $updateData['jam_praktik'] = $data['jam_praktik'];
        }
        
        // Handle hari_praktik if provided
        if (isset($data['hari_praktik'])) {
            $updateData['hari_praktik'] = $data['hari_praktik'];
        }

        return $this->updateDokter($dokter, $updateData);
    }
}
