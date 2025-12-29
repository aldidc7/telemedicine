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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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

            // Check if it's email verification issue
            $user = User::where('email', $data['email'])->first();
            if ($user && ($user->role === 'dokter' || $user->role === 'admin') && !$user->email_verified_at) {
                return $this->validationErrorResponse(
                    'Email belum diverifikasi. Silakan cek email Anda untuk link verifikasi.',
                    403,
                    ['error_code' => 'EMAIL_NOT_VERIFIED']
                );
            }

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
        // Validate berdasarkan method
        $method = $request->method ?? 'email';

        if ($method === 'whatsapp') {
            $request->validate([
                'phone' => ['required', 'regex:/^(\+62|0)[0-9]{9,12}$/'],
                'method' => 'sometimes|in:email,whatsapp',
            ]);
            $identifier = $request->phone;
        } else {
            $request->validate([
                'email' => 'required|email',
                'method' => 'sometimes|in:email,whatsapp',
            ]);
            $identifier = $request->email;
        }

        // Rate limiting check
        $key = "forgot-password:{$identifier}";

        if (RateLimitService::isLimited($key, RateLimitService::FORGOT_PASSWORD_MAX_ATTEMPTS, RateLimitService::FORGOT_PASSWORD_DECAY_MINUTES)) {
            return $this->validationErrorResponse('Terlalu banyak upaya reset password. Silakan coba lagi nanti.', 429, [
                'retry_after' => RateLimitService::FORGOT_PASSWORD_DECAY_MINUTES * 60,
            ]);
        }

        RateLimitService::increment($key, RateLimitService::FORGOT_PASSWORD_DECAY_MINUTES);

        if ($method === 'whatsapp') {
            $result = $this->authService->forgotPasswordWhatsApp($request->phone);
        } else {
            $result = $this->authService->forgotPassword($request->email, 'email');
        }

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

    /**
     * Verify email dengan token
     * 
     * POST /api/v1/auth/verify-email-confirm
     * 
     * Body:
     * {
     *   "token": "verification_token_dari_email"
     * }
     */
    public function verifyEmailConfirm(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $success = $this->authService->verifyEmail($request->token);

        if (!$success) {
            return $this->validationErrorResponse(
                'Token verifikasi tidak valid atau sudah expired. Silakan minta link baru.',
                422
            );
        }

        return $this->successResponse(
            null,
            'Email berhasil diverifikasi. Anda sekarang bisa login.'
        );
    }

    /**
     * Resend email verification token
     * 
     * POST /api/v1/auth/resend-verification
     * 
     * Body:
     * {
     *   "email": "user@example.com"
     * }
     */
    public function resendVerification(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return $this->notFoundResponse('User tidak ditemukan');
        }

        // Check if already verified
        if ($user->email_verified_at) {
            return $this->successResponse(
                null,
                'Email sudah diverifikasi sebelumnya'
            );
        }

        // Generate new token
        $token = Str::random(64);
        $user->update([
            'email_verification_token' => $token,
            'email_verification_expires_at' => now()->addHours(24),
        ]);

        // Send email (implement mail sending)
        // Mail::send('emails.verify-email', ['user' => $user, 'token' => $token], function ($message) use ($user) {
        //     $message->to($user->email)->subject('Verifikasi Email - Telemedicine');
        // });

        Log::info('Email verification token sent to: ' . $user->email);

        return $this->successResponse(
            null,
            'Link verifikasi telah dikirim ke email Anda'
        );
    }

    /**
     * Logout from all devices
     * 
     * POST /api/v1/auth/logout-all
     * 
     * Invalidates all active sessions for the current user
     */
    public function logoutAll(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return $this->unauthorizedResponse('User tidak ditemukan');
        }

        // Deactivate all active sessions
        \App\Models\UserSession::where('user_id', $user->id)
            ->where('is_active', true)
            ->update([
                'is_active' => false,
                'expires_at' => now()
            ]);

        // Call auth service logout
        $this->authService->logout();

        return $this->successResponse(null, 'Logout dari semua perangkat berhasil');
    }

    /**
     * Get consent status for current user
     * 
     * GET /api/v1/auth/consent-status
     */
    public function getConsentStatus(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return $this->unauthorizedResponse('User tidak ditemukan');
        }

        // Admin tidak perlu consent
        if ($user->role === 'admin') {
            return $this->successResponse([
                'accepted_consents' => [],
                'requires_consent' => false
            ], 'Admin tidak memerlukan consent');
        }

        // For now, no consent required (can be expanded later)
        return $this->successResponse([
            'accepted_consents' => [],
            'requires_consent' => false
        ], 'Consent status retrieved');
    }

    /**
     * Accept consent
     * 
     * POST /api/v1/auth/accept-consent
     */
    public function acceptConsent(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return $this->unauthorizedResponse('User tidak ditemukan');
        }

        $consentType = $request->input('consent_type');

        if (!$consentType) {
            return $this->validationErrorResponse('Consent type harus diisi');
        }

        // Log consent acceptance
        \App\Models\ActivityLog::log($user->id, 'accept_consent', 'Consent type: ' . $consentType);

        return $this->successResponse([
            'accepted_consents' => [$consentType],
            'message' => 'Terima kasih telah menerima consent'
        ], 'Consent berhasil diterima');
    }

    /**
     * Verifikasi OTP untuk password reset via WhatsApp
     * 
     * POST /api/v1/auth/verify-otp
     * 
     * Request body:
     * {
     *   "phone": "+628888881234",
     *   "otp": "123456"
     * }
     */
    public function verifyOtp(Request $request)
    {
        // Validate input
        $request->validate([
            'phone' => ['required', 'regex:/^(\+62|0)[0-9]{9,12}$/'],
            'otp' => 'required|string|size:6|regex:/^\d{6}$/',
        ]);

        $phone = $request->phone;
        $otp = $request->otp;

        // Rate limiting check
        $key = "verify-otp:{$phone}";

        if (RateLimitService::isLimited($key, RateLimitService::VERIFY_OTP_MAX_ATTEMPTS, RateLimitService::VERIFY_OTP_DECAY_MINUTES)) {
            return $this->validationErrorResponse('Terlalu banyak upaya verifikasi OTP. Silakan coba lagi nanti.', 429, [
                'retry_after' => RateLimitService::VERIFY_OTP_DECAY_MINUTES * 60,
            ]);
        }

        RateLimitService::increment($key, RateLimitService::VERIFY_OTP_DECAY_MINUTES);

        // Call service to verify OTP
        $result = $this->authService->verifyOtp($phone, $otp);

        if (!$result['success']) {
            return $this->validationErrorResponse($result['message'], 422);
        }

        return $this->successResponse([
            'reset_token' => $result['reset_token'] ?? null,
            'message' => 'OTP berhasil diverifikasi'
        ], 'OTP berhasil diverifikasi');
    }

    /**
     * Kirim ulang OTP untuk password reset via WhatsApp
     * 
     * POST /api/v1/auth/resend-otp
     * 
     * Request body:
     * {
     *   "phone": "+628888881234"
     * }
     */
    public function resendOtp(Request $request)
    {
        // Validate input
        $request->validate([
            'phone' => ['required', 'regex:/^(\+62|0)[0-9]{9,12}$/'],
        ]);

        $phone = $request->phone;

        // Rate limiting check
        $key = "resend-otp:{$phone}";

        if (RateLimitService::isLimited($key, RateLimitService::RESEND_OTP_MAX_ATTEMPTS, RateLimitService::RESEND_OTP_DECAY_MINUTES)) {
            return $this->validationErrorResponse('Terlalu banyak permintaan resend OTP. Silakan coba lagi nanti.', 429, [
                'retry_after' => RateLimitService::RESEND_OTP_DECAY_MINUTES * 60,
            ]);
        }

        RateLimitService::increment($key, RateLimitService::RESEND_OTP_DECAY_MINUTES);

        // Call service to resend OTP
        $result = $this->authService->forgotPasswordWhatsApp($phone);

        return $this->successResponse(null, 'Kode OTP telah dikirim ulang');
    }

    /**
     * Debug endpoint - Get OTP code for testing
     * FOR DEVELOPMENT/TESTING ONLY
     * 
     * GET /api/v1/auth/debug/get-otp?phone=08888881234
     */
    public function debugGetOtp(Request $request)
    {
        // Only allow in development environment
        if (config('app.env') === 'production') {
            return $this->validationErrorResponse('Endpoint tidak tersedia di production', 403);
        }

        $request->validate([
            'phone' => ['required', 'regex:/^(\+62|0)[0-9]{9,12}$/'],
        ]);

        $phone = $request->phone;

        // Normalize phone number
        $phone = preg_replace('/^0/', '62', $phone);
        if (strpos($phone, '+') === 0) {
            $phone = substr($phone, 1);
        }

        // Find user by phone
        $user = User::where('phone_number', 'like', "%{$phone}%")
            ->orWhere('phone_number', 'like', "%0" . substr($phone, 2) . "%")
            ->first();

        if (!$user || !$user->password_reset_token) {
            return $this->errorResponse('Tidak ada OTP untuk nomor ini atau belum request reset password', 404);
        }

        // Check if token expired
        if (now()->isAfter($user->password_reset_expires_at)) {
            return $this->errorResponse('OTP sudah expired', 410);
        }

        $otp = substr($user->password_reset_token, 0, 6);
        $expiresAt = $user->password_reset_expires_at;
        $timeRemaining = now()->diffInSeconds($expiresAt, false);

        return $this->successResponse([
            'phone' => $user->phone_number,
            'otp' => $otp,
            'expires_at' => $expiresAt,
            'time_remaining_seconds' => max(0, $timeRemaining),
            'reset_token' => $user->password_reset_token,
        ], 'OTP debug info (development only)');
    }

    /**
     * Test Twilio WhatsApp Configuration
     * FOR DEVELOPMENT ONLY
     * 
     * GET /api/v1/auth/test/twilio-status
     */
    public function testTwilioStatus()
    {
        // Only allow in development environment
        if (config('app.env') === 'production') {
            return $this->validationErrorResponse('Endpoint tidak tersedia di production', 403);
        }

        $whatsappService = app(\App\Services\WhatsAppService::class);
        $status = $whatsappService->getStatus();

        return $this->successResponse($status, 'Twilio WhatsApp configuration status');
    }

    /**
     * Send Test WhatsApp Message
     * FOR DEVELOPMENT ONLY
     * 
     * POST /api/v1/auth/test/send-whatsapp
     * 
     * Request body:
     * {
     *   "phone": "+62888881234",
     *   "message": "Test message"
     * }
     */
    public function testSendWhatsApp(Request $request)
    {
        // Only allow in development environment
        if (config('app.env') === 'production') {
            return $this->validationErrorResponse('Endpoint tidak tersedia di production', 403);
        }

        $request->validate([
            'phone' => ['required', 'regex:/^(\+62|0)[0-9]{9,12}$/'],
            'message' => 'sometimes|string|max:1000',
        ]);

        $phone = $request->phone;
        $message = $request->message ?? 'Test WhatsApp message dari Telemedicine API';

        // Normalize phone ke format +62xxx
        if (strpos($phone, '0') === 0) {
            $phone = '+62' . substr($phone, 1);
        } elseif (strpos($phone, '+') !== 0) {
            $phone = '+' . $phone;
        }

        try {
            $whatsappService = app(\App\Services\WhatsAppService::class);

            if (!$whatsappService->isConfigured()) {
                return $this->errorResponse('Twilio WhatsApp belum dikonfigurasi. Lihat TWILIO_SETUP_GUIDE.md', 400);
            }

            $sent = $whatsappService->sendOtp($phone, 'TEST');

            if ($sent) {
                return $this->successResponse([
                    'phone' => $phone,
                    'status' => 'sent',
                    'message' => 'Test WhatsApp message sedang dikirim. Cek WhatsApp Anda dalam 1-2 detik.'
                ], 'Test message sent successfully');
            } else {
                return $this->errorResponse('Gagal mengirim test WhatsApp message. Cek logs untuk detail.', 400);
            }
        } catch (\Exception $e) {
            return $this->errorResponse('Error: ' . $e->getMessage(), 400);
        }
    }
}
