<?php

namespace App\Services;

use App\Models\User;
use App\Models\Pasien;
use App\Models\Dokter;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Authentication Service
 * 
 * Handle semua logika autentikasi termasuk login, register, logout
 */
class AuthService
{
    /**
     * Register user baru (Pasien atau Dokter)
     *
     * @param array $data
     * @return array [user, token]
     */
    public function register(array $data): array
    {
        $result = DB::transaction(function () use ($data) {
            // Create user
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => $data['role'] ?? 'pasien',
                'is_active' => true,
                'last_login_at' => now(),
            ]);

            // Create role-specific data
            if ($user->role === 'pasien') {
                Pasien::create([
                    'user_id' => $user->id,
                    'nik' => $data['nik'] ?? null,
                    'date_of_birth' => $data['tanggal_lahir'] ?? null,
                    'gender' => $data['jenis_kelamin'] ?? null,
                    'address' => $data['alamat'] ?? null,
                    'phone_number' => $data['phone'] ?? null,
                    'blood_type' => $data['golongan_darah'] ?? null,
                    'emergency_contact_name' => $data['nama_kontak_darurat'] ?? null,
                    'emergency_contact_phone' => $data['no_kontak_darurat'] ?? null,
                ]);
            } elseif ($user->role === 'dokter') {
                Dokter::create([
                    'user_id' => $user->id,
                    'specialization' => $data['spesialisasi'] ?? '',
                    'license_number' => $data['no_lisensi'] ?? '',
                    'phone_number' => $data['phone'] ?? null,
                    'is_available' => true,
                    'max_concurrent_consultations' => 5,
                ]);
            }

            return $user;
        });

        // Generate token
        $token = $result->createToken('api-token')->plainTextToken;

        return [
            'user' => $result,
            'token' => $token,
            'token_type' => 'Bearer',
        ];
    }

    /**
     * Login user dengan email, username, atau NIK
     *
     * @param string $identifier (Email, username, atau NIK)
     * @param string $password
     * @return array|null
     */
    public function login(string $identifier, string $password): ?array
    {
        $user = User::where('email', $identifier)
            ->orWhere('email', 'like', $identifier)  // Support username login
            ->orWhereHas('pasien', function ($query) use ($identifier) {
                $query->where('nik', $identifier);
            })
            ->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return null;
        }

        // Check if user is active
        if (!$user->is_active) {
            return null;
        }

        // Update last login
        $user->update(['last_login_at' => now()]);

        // Generate token using Sanctum
        $token = $user->createToken('api-token')->plainTextToken;

        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'token' => $token,
            'token_type' => 'Bearer',
        ];
    }

    /**
     * Logout user (revoke token)
     *
     * @return bool
     */
    public function logout(): bool
    {
        $user = Auth::user();
        
        if ($user) {
            // Revoke all tokens
            $user->tokens()->delete();
            return true;
        }

        return false;
    }

    /**
     * Get current authenticated user
     *
     * @return User|null
     */
    public function getCurrentUser(): ?User
    {
        return Auth::user();
    }

    /**
     * Update user profile
     *
     * @param User $user
     * @param array $data
     * @return User
     */
    public function updateProfile(User $user, array $data): User
    {
        $updateData = [];

        if (isset($data['name'])) {
            $updateData['name'] = $data['name'];
        }

        if (isset($data['email']) && $data['email'] !== $user->email) {
            $updateData['email'] = $data['email'];
        }

        if (isset($data['phone'])) {
            $updateData['phone'] = $data['phone'];
        }

        if (isset($data['alamat'])) {
            $updateData['alamat'] = $data['alamat'];
        }

        if (isset($data['tanggal_lahir'])) {
            $updateData['tanggal_lahir'] = $data['tanggal_lahir'];
        }

        if (isset($data['jenis_kelamin'])) {
            $updateData['jenis_kelamin'] = $data['jenis_kelamin'];
        }

        if (isset($data['spesialisasi']) && $user->role === 'dokter') {
            $updateData['spesialisasi'] = $data['spesialisasi'];
        }

        $user->update($updateData);

        return $user->fresh();
    }

    /**
     * Change user password
     *
     * @param User $user
     * @param string $currentPassword
     * @param string $newPassword
     * @return bool
     */
    public function changePassword(User $user, string $currentPassword, string $newPassword): bool
    {
        if (!Hash::check($currentPassword, $user->password)) {
            return false;
        }

        $user->update([
            'password' => Hash::make($newPassword),
        ]);

        // Revoke all tokens untuk security
        $user->tokens()->delete();

        return true;
    }
}
