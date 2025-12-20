<?php

namespace App\Services;

use App\Models\User;
use App\Models\Pasien;
use App\Models\Dokter;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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
            // Generate email verification token
            $verificationToken = Str::random(64);

            // Create user
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => $data['role'] ?? 'pasien',
                'is_active' => true,
                'last_login_at' => now(),
                'email_verification_token' => $verificationToken,
                'email_verification_expires_at' => now()->addHours(24),  // Token expires in 24 hours
            ]);

            // Create role-specific data
            if ($user->role === 'pasien') {
                Pasien::create([
                    'user_id' => $user->id,
                    'nik' => $data['nik'] ?? null,
                    'date_of_birth' => $data['date_of_birth'] ?? $data['tanggal_lahir'] ?? now()->subYears(25)->format('Y-m-d'),
                    'gender' => $data['gender'] ?? $data['jenis_kelamin'] ?? 'laki-laki',
                    'address' => $data['address'] ?? $data['alamat'] ?? '-',
                    'phone_number' => $data['phone'] ?? '-',
                    'blood_type' => $data['blood_type'] ?? $data['golongan_darah'] ?? null,
                    'emergency_contact_name' => $data['emergency_contact_name'] ?? $data['nama_kontak_darurat'] ?? null,
                    'emergency_contact_phone' => $data['emergency_contact_phone'] ?? $data['no_kontak_darurat'] ?? null,
                ]);
            } elseif ($user->role === 'dokter') {
                Dokter::create([
                    'user_id' => $user->id,
                    'specialization' => $data['specialization'] ?? null,  // Optional, dapat diisi di profile edit
                    'license_number' => $data['sip'] ?? '',
                    'phone_number' => $data['phone'] ?? null,
                    'is_available' => true,
                    'is_verified' => false,  // Dokter baru belum terverifikasi
                    'max_concurrent_consultations' => 5,
                ]);
            }

            return $user;
        });

        // Email verification removed - focus on chat system only

        // Generate token
        $token = $result->createToken('api-token')->plainTextToken;

        return [
            'user' => $result,
            'token' => $token,
            'token_type' => 'Bearer',
            'message' => 'Registrasi berhasil. Silakan verifikasi email Anda.',
        ];
    }

    /**
     * Login user dengan email dan password
     *
     * @param string $email
     * @param string $password
     * @return array|null
     */
    public function login(string $email, string $password): ?array
    {
        $user = User::where('email', $email)->with(['dokter', 'pasien'])->first();

        if (!$user || !Hash::check($password, $user->password)) {
            // Log failed login attempt
            ActivityLog::log(null, 'login_failed', 'Email: ' . $email);
            return null;
        }

        // Check if user is active
        if (!$user->is_active) {
            ActivityLog::log($user->id, 'login_failed', 'User is inactive');
            return null;
        }

        // Check email verification - MANDATORY for doctors only
        // Admin doesn't need email verification
        if ($user->role === 'dokter' && !$user->email_verified_at) {
            ActivityLog::log($user->id, 'login_failed', 'Email not verified');
            return null; // Will return error with message in controller
        }

        // Update last login
        $user->update(['last_login_at' => now()]);

        // Log successful login
        ActivityLog::log($user->id, 'login', 'User logged in');

        // Generate token using Sanctum
        $token = $user->createToken('api-token')->plainTextToken;

        // Create session tracking record
        try {
            \App\Models\UserSession::create([
                'user_id' => $user->id,
                'token' => $token,
                'ip_address' => \request()->ip(),
                'user_agent' => \request()->header('User-Agent'),
                'device_name' => null,
                'expires_at' => now()->addDays(7),  // Token valid for 7 days
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create session: ' . $e->getMessage());
            // Continue even if session creation fails
        }

        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'dokter' => $user->dokter,
                'pasien' => $user->pasien,
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
            // Log logout
            ActivityLog::log($user->id, 'logout', 'User logged out');

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
        return Auth::user()?->load(['dokter', 'pasien']);
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
            $updateData['specialization'] = $data['spesialisasi'];
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
            ActivityLog::log($user->id, 'password_change_failed', 'Current password is incorrect');
            return false;
        }

        $user->update([
            'password' => Hash::make($newPassword),
        ]);

        // Log password change
        ActivityLog::log($user->id, 'password_changed', 'User changed password');

        // Revoke all tokens untuk security
        $user->tokens()->delete();

        return true;
    }

    /**
     * Verify user email dengan token
     *
     * @param string $token - Email verification token
     * @return bool - True jika berhasil, false jika token invalid
     */
    public function verifyEmail(string $token): bool
    {
        $user = User::where('email_verification_token', $token)->first();

        if (!$user) {
            return false;
        }

        // Check if token has expired
        if ($user->email_verification_expires_at && $user->email_verification_expires_at->isPast()) {
            return false;
        }

        $user->update([
            'email_verified_at' => now(),
            'email_verification_token' => null,
            'email_verification_expires_at' => null,
        ]);

        // Create notification
        try {
            $notificationService = app(NotificationService::class);
            $notificationService->notifyEmailVerified($user->id);
        } catch (\Exception $e) {
            Log::warning('Failed to create notification: ' . $e->getMessage());
        }

        return true;
    }

    /**
     * Generate password reset token dan send email
     *
     * @param string $email - Email user
     * @return array - ['success' => bool, 'message' => string]
     */
    public function forgotPassword(string $email): array
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            // Return generic message untuk security (don't reveal if email exists)
            return [
                'success' => true,
                'message' => 'Jika email terdaftar, Anda akan menerima link reset password',
            ];
        }

        try {
            // Generate reset token (64 chars)
            $resetToken = Str::random(64);

            // Update user dengan reset token dan expiry (2 hours)
            $user->update([
                'password_reset_token' => $resetToken,
                'password_reset_expires_at' => now()->addHours(2),
            ]);

            // Send password reset email
            \Illuminate\Support\Facades\Mail::send(new \App\Mail\PasswordResetMail($user, $resetToken));

            Log::info('Password reset email sent to: ' . $user->email);

            return [
                'success' => true,
                'message' => 'Jika email terdaftar, Anda akan menerima link reset password',
            ];
        } catch (\Exception $e) {
            Log::error('Failed to send password reset email: ' . $e->getMessage());

            return [
                'success' => true,
                'message' => 'Jika email terdaftar, Anda akan menerima link reset password',
            ];
        }
    }

    /**
     * Reset password dengan token
     *
     * @param string $token - Password reset token
     * @param string $newPassword - Password baru
     * @return array - ['success' => bool, 'message' => string]
     */
    public function resetPassword(string $token, string $newPassword): array
    {
        $user = User::where('password_reset_token', $token)->first();

        if (!$user) {
            return [
                'success' => false,
                'message' => 'Token reset password tidak valid',
            ];
        }

        // Check jika token sudah expired
        if ($user->password_reset_expires_at && $user->password_reset_expires_at->isPast()) {
            return [
                'success' => false,
                'message' => 'Token reset password sudah kadaluarsa. Silakan request reset password lagi',
            ];
        }

        try {
            // Update password dan clear reset token
            $user->update([
                'password' => Hash::make($newPassword),
                'password_reset_token' => null,
                'password_reset_expires_at' => null,
            ]);

            // Log password reset
            ActivityLog::log($user->id, 'password_reset', 'User reset password via token');

            // Revoke all tokens untuk security
            $user->tokens()->delete();

            return [
                'success' => true,
                'message' => 'Password berhasil direset. Silakan login dengan password baru',
            ];
        } catch (\Exception $e) {
            Log::error('Failed to reset password: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mereset password. Silakan coba lagi',
            ];
        }
    }
}
