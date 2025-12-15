<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ProfileUpdateRequest;
use App\Services\AuthService;
use App\Services\RateLimitService;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * ============================================
 * KONTROLER AUTENTIKASI - REGISTER & LOGIN
 * ============================================
 * 
 * File ini berfungsi untuk:
 * - Register user baru (pasien atau dokter)
 * - Login dengan email dan password
 * - Refresh token JWT
 * - Logout dan invalidate token
 * - Ambil profil user login
 * 
 * Endpoint:
 * POST /api/v1/auth/register
 * POST /api/v1/auth/login
 * GET /api/v1/auth/me
 * POST /api/v1/auth/refresh
 * POST /api/v1/auth/logout
 * 
 * @author Aplikasi Telemedicine RSUD dr. R. Soedarsono
 * @version 2.0
 */
class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(
        private AuthService $authService
    ) {}

    /**
     * Register user baru (Pasien atau Dokter)
     */
    public function register(RegisterRequest $request)
    {
        // Rate limiting check by email
        $email = $request->email;
        $key = "register:{$email}";
        
        if (RateLimitService::isLimited($key, RateLimitService::REGISTER_MAX_ATTEMPTS, RateLimitService::REGISTER_DECAY_MINUTES)) {
            return $this->validationErrorResponse('Terlalu banyak upaya registrasi. Silakan coba lagi nanti.', 429, [
                'retry_after' => RateLimitService::REGISTER_DECAY_MINUTES * 60,
            ]);
        }
        
        // Validation happens automatically in RegisterRequest
        $result = $this->authService->register($request->validated());
        
        // Reset rate limit on successful registration
        RateLimitService::reset($key);
        
        return $this->createdResponse(
            $result,
            'User berhasil terdaftar'
        );
    }

    /**
     * Login user dengan email dan password
     */
    public function login(LoginRequest $request)
    {
        // Rate limiting check
        $email = $request->email;
        $ip = $request->ip();
        $key = "login:{$email}:{$ip}";
        
        if (RateLimitService::isLimited($key, RateLimitService::LOGIN_MAX_ATTEMPTS, RateLimitService::LOGIN_DECAY_MINUTES)) {
            $remaining = RateLimitService::remaining($key, RateLimitService::LOGIN_MAX_ATTEMPTS, RateLimitService::LOGIN_DECAY_MINUTES);
            return $this->validationErrorResponse('Terlalu banyak upaya login. Silakan coba lagi dalam 15 menit.', 429, [
                'retry_after' => RateLimitService::LOGIN_DECAY_MINUTES * 60,
                'remaining' => $remaining,
            ]);
        }
        
        // Validation happens automatically in LoginRequest
        $data = $request->validated();
        
        $result = $this->authService->login(
            $data['email'],
            $data['password']
        );
        
        if (!$result) {
            // Increment rate limit on failed attempt
            RateLimitService::increment($key, RateLimitService::LOGIN_DECAY_MINUTES);
            
            $remaining = RateLimitService::remaining($key, RateLimitService::LOGIN_MAX_ATTEMPTS, RateLimitService::LOGIN_DECAY_MINUTES);
            return $this->unauthorizedResponse('Email atau password salah', null, [
                'remaining_attempts' => $remaining,
            ]);
        }
        
        // Reset rate limit on successful login
        RateLimitService::reset($key);
        
        return $this->successResponse(
            $result,
            'Login berhasil'
        );
    }

    /**
     * Ambil data profil user yang sedang login
     * 
     * Fungsi ini mengembalikan detail user termasuk role-specific data.
     * Untuk pasien: Include data pasien (NIK, TTL, alamat, dll)
     * Untuk dokter: Include data dokter (spesialisasi, lisensi, status)
     * 
     * GET /api/v1/auth/me
     * 
     * Header Required:
     * - Authorization: Bearer {token}
     * 
     * @param Request $request - HTTP request (authenticated)
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * Get current user profile
     */
    public function me(Request $request)
    {
        $user = $this->authService->getCurrentUser();
        
        if (!$user) {
            return $this->unauthorizedResponse('User tidak ditemukan');
        }
        
        return $this->successResponse($user, 'Profil user berhasil diambil');
    }

    /**
     * Get profile completion status
     * 
     * GET /api/v1/auth/profile-completion
     * 
     * Returns percentage completion of user profile
     */
    public function profileCompletion(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return $this->unauthorizedResponse('User tidak ditemukan');
        }
        
        $completion = \App\Services\ProfileCompletionService::getCompletion($user);
        
        return $this->successResponse($completion, 'Status kelengkapan profil berhasil diambil');
    }

    /**
     * Refresh JWT token yang sudah expire
     * 
     * Fungsi ini membuat token baru untuk user yang sudah login.
     * Gunakan ketika token lama mendekati expire.
     * 
     * POST /api/v1/auth/refresh
     * 
     * Header Required:
     * - Authorization: Bearer {old_token}
     * 
     * @param Request $request - HTTP request (authenticated)
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * Refresh JWT token
     * 
     * POST /api/v1/auth/refresh
     */
    public function refreshToken(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return $this->unauthorizedResponse('User tidak ditemukan');
        }
        
        $token = $user->createToken('auth_token')->plainTextToken;
        
        return $this->successResponse(
            ['token' => $token],
            'Token berhasil diperbarui'
        );
    }

    /**
     * Logout user dan invalidate token
     * 
     * POST /api/v1/auth/logout
     */
    public function logout(Request $request)
    {
        $this->authService->logout();
        
        return $this->successResponse(null, 'Logout berhasil');
    }

    /**
     * Verify email dengan token
     * 
     * GET /api/v1/auth/verify-email?token=xxx
     */
    public function verifyEmail(Request $request)
    {
        $token = $request->query('token');
        
        if (!$token) {
            return $this->validationErrorResponse('Token tidak ditemukan');
        }

        $verified = $this->authService->verifyEmail($token);

        if (!$verified) {
            return $this->notFoundResponse('Token verifikasi tidak valid atau sudah kadaluarsa');
        }

        return $this->successResponse(null, 'Email berhasil diverifikasi. Anda sekarang bisa login.');
    }

    /**
     * Request password reset
     * 
     * POST /api/v1/auth/forgot-password
     * 
     * Request body:
     * {
     *   "email": "user@example.com"
     * }
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        // Rate limiting check
        $email = $request->email;
        $key = "forgot-password:{$email}";
        
        if (RateLimitService::isLimited($key, RateLimitService::FORGOT_PASSWORD_MAX_ATTEMPTS, RateLimitService::FORGOT_PASSWORD_DECAY_MINUTES)) {
            return $this->validationErrorResponse('Terlalu banyak upaya reset password. Silakan coba lagi nanti.', 429, [
                'retry_after' => RateLimitService::FORGOT_PASSWORD_DECAY_MINUTES * 60,
            ]);
        }
        
        RateLimitService::increment($key, RateLimitService::FORGOT_PASSWORD_DECAY_MINUTES);
        $result = $this->authService->forgotPassword($request->email);

        return $this->successResponse(null, $result['message']);
    }

    /**
     * Reset password dengan token
     * 
     * POST /api/v1/auth/reset-password
     * 
     * Request body:
     * {
     *   "token": "xxx...",
     *   "password": "NewPassword123!",
     *   "password_confirmation": "NewPassword123!"
     * }
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[a-zA-Z\d@$!%*?&]+$/',
                'confirmed',
            ],
        ], [
            'password.regex' => 'Password harus mengandung huruf besar, kecil, angka, dan simbol (@$!%*?&)',
        ]);

        $result = $this->authService->resetPassword(
            $request->token,
            $request->password
        );

        if (!$result['success']) {
            return $this->validationErrorResponse($result['message']);
        }

        return $this->successResponse(null, $result['message']);
    }
}