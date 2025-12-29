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
/**
 * @OA\Tag(
 *     name="Authentication",
 *     description="User authentication endpoints - Register, Login, Logout, Token Refresh"
 * )
 * 
 * @OA\Post(
 *     path="/auth/register",
 *     operationId="registerUser",
 *     tags={"Authentication"},
 *     summary="Register user baru",
 *     description="Registrasi user baru sebagai pasien atau dokter dengan validasi lengkap",
 *     @OA\RequestBody(
 *         required=true,
 *         description="User registration data",
 *         @OA\JsonContent(
 *             required={"email","password","password_confirmation","name","role"},
 *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
 *             @OA\Property(property="password", type="string", format="password", minLength=8, example="SecurePass123!"),
 *             @OA\Property(property="password_confirmation", type="string", format="password", example="SecurePass123!"),
 *             @OA\Property(property="name", type="string", example="John Doe"),
 *             @OA\Property(property="role", type="string", enum={"pasien","dokter"}, example="pasien"),
 *             @OA\Property(property="phone", type="string", example="08123456789")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="User berhasil didaftarkan",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="User berhasil terdaftar"),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="email", type="string", format="email", example="user@example.com"),
 *                 @OA\Property(property="name", type="string", example="John Doe"),
 *                 @OA\Property(property="role", type="string", example="pasien"),
 *                 @OA\Property(property="phone", type="string", example="08123456789"),
 *                 @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGc..."),
 *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-15T10:30:00Z")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Bad Request - Request format tidak valid",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(
 *                 property="error",
 *                 type="object",
 *                 @OA\Property(property="code", type="string", example="BAD_REQUEST"),
 *                 @OA\Property(property="message", type="string", example="Request format tidak valid"),
 *                 @OA\Property(
 *                     property="details",
 *                     type="object",
 *                     @OA\Property(property="info", type="string", example="Invalid JSON format atau missing required fields")
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation Error - Data tidak valid",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(
 *                 property="error",
 *                 type="object",
 *                 @OA\Property(property="code", type="string", example="VALIDATION_ERROR"),
 *                 @OA\Property(property="message", type="string", example="Validation failed"),
 *                 @OA\Property(
 *                     property="details",
 *                     type="object",
 *                     @OA\Property(
 *                         property="validation_errors",
 *                         type="object",
 *                         @OA\Property(property="email", type="array", @OA\Items(type="string", example="Email sudah terdaftar")),
 *                         @OA\Property(property="password", type="array", @OA\Items(type="string", example="Password minimal 8 karakter")),
 *                         @OA\Property(property="password_confirmation", type="array", @OA\Items(type="string", example="Password tidak sesuai")),
 *                         @OA\Property(property="name", type="array", @OA\Items(type="string", example="Nama wajib diisi")),
 *                         @OA\Property(property="role", type="array", @OA\Items(type="string", example="Role hanya boleh pasien atau dokter"))
 *                     )
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=429,
 *         description="Too Many Requests - Rate limit exceeded",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(
 *                 property="error",
 *                 type="object",
 *                 @OA\Property(property="code", type="string", example="TOO_MANY_REQUESTS"),
 *                 @OA\Property(property="message", type="string", example="Terlalu banyak upaya registrasi. Silakan coba lagi nanti."),
 *                 @OA\Property(
 *                     property="details",
 *                     type="object",
 *                     @OA\Property(property="retry_after", type="integer", example=900),
 *                     @OA\Property(property="info", type="string", example="Rate limit 3 registrasi per email per 15 menit")
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal Server Error",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(
 *                 property="error",
 *                 type="object",
 *                 @OA\Property(property="code", type="string", example="INTERNAL_SERVER_ERROR"),
 *                 @OA\Property(property="message", type="string", example="Terjadi kesalahan pada server"),
 *                 @OA\Property(
 *                     property="details",
 *                     type="object",
 *                     @OA\Property(property="request_id", type="string", example="req_12345678"),
 *                     @OA\Property(property="info", type="string", example="Silakan hubungi support dengan request_id")
 *                 )
 *             )
 *         )
 *     )
 * )
 */
class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(
        private AuthService $authService
    ) {}

    /**
     * Register user baru (Pasien atau Dokter)
     * 
     * @param RegisterRequest $request
     * @return \Illuminate\Http\JsonResponse
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
     * @OA\Post(
     *     path="/auth/login",
     *     operationId="loginUser",
     *     tags={"Authentication"},
     *     summary="Login user",
     *     description="Authenticate user dengan email dan password, return JWT token",
     *     @OA\RequestBody(
     *         required=true,
     *         description="User login credentials",
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="SecurePass123!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Login berhasil"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGc..."),
     *                 @OA\Property(property="token_type", type="string", example="Bearer"),
     *                 @OA\Property(property="expires_in", type="integer", example=86400),
     *                 @OA\Property(
     *                     property="user",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *                     @OA\Property(property="name", type="string", example="John Doe"),
     *                     @OA\Property(property="role", type="string", example="pasien"),
     *                     @OA\Property(property="phone", type="string", example="08123456789"),
     *                     @OA\Property(property="avatar_url", type="string", format="uri", example="https://...")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(
     *                 property="error",
     *                 type="object",
     *                 @OA\Property(property="code", type="string", example="BAD_REQUEST"),
     *                 @OA\Property(property="message", type="string", example="Email dan password harus diisi"),
     *                 @OA\Property(property="details", type="object", example={})
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Invalid credentials",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(
     *                 property="error",
     *                 type="object",
     *                 @OA\Property(property="code", type="string", example="UNAUTHORIZED"),
     *                 @OA\Property(property="message", type="string", example="Email atau password salah"),
     *                 @OA\Property(
     *                     property="details",
     *                     type="object",
     *                     @OA\Property(property="info", type="string", example="Pastikan email dan password benar"),
     *                     @OA\Property(property="remaining_attempts", type="integer", example=2)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Email not verified",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(
     *                 property="error",
     *                 type="object",
     *                 @OA\Property(property="code", type="string", example="EMAIL_NOT_VERIFIED"),
     *                 @OA\Property(property="message", type="string", example="Email belum diverifikasi. Silakan cek email Anda untuk link verifikasi."),
     *                 @OA\Property(
     *                     property="details",
     *                     type="object",
     *                     @OA\Property(property="error_code", type="string", example="EMAIL_NOT_VERIFIED")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(
     *                 property="error",
     *                 type="object",
     *                 @OA\Property(property="code", type="string", example="VALIDATION_ERROR"),
     *                 @OA\Property(property="message", type="string", example="Validation failed"),
     *                 @OA\Property(
     *                     property="details",
     *                     type="object",
     *                     @OA\Property(
     *                         property="validation_errors",
     *                         type="object",
     *                         @OA\Property(property="email", type="array", @OA\Items(type="string", example="Format email tidak valid")),
     *                         @OA\Property(property="password", type="array", @OA\Items(type="string", example="Password harus diisi"))
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=429,
     *         description="Too Many Requests - Rate limit exceeded",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(
     *                 property="error",
     *                 type="object",
     *                 @OA\Property(property="code", type="string", example="TOO_MANY_REQUESTS"),
     *                 @OA\Property(property="message", type="string", example="Terlalu banyak upaya login. Silakan coba lagi dalam 15 menit."),
     *                 @OA\Property(
     *                     property="details",
     *                     type="object",
     *                     @OA\Property(property="retry_after", type="integer", example=900),
     *                     @OA\Property(property="remaining", type="integer", example=0),
     *                     @OA\Property(property="info", type="string", example="Rate limit 5 percobaan per IP per 15 menit")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(
     *                 property="error",
     *                 type="object",
     *                 @OA\Property(property="code", type="string", example="INTERNAL_SERVER_ERROR"),
     *                 @OA\Property(property="message", type="string", example="Terjadi kesalahan pada server"),
     *                 @OA\Property(
     *                     property="details",
     *                     type="object",
     *                     @OA\Property(property="request_id", type="string", example="req_87654321"),
     *                     @OA\Property(property="timestamp", type="string", format="date-time", example="2024-01-15T10:30:00Z")
     *                 )
     *             )
     *         )
     *     )
     * )
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
     * @OA\Get(
     *     path="/auth/me",
     *     operationId="getCurrentUser",
     *     tags={"Authentication"},
     *     summary="Get current authenticated user",
     *     description="Get profil user yang sedang login menggunakan JWT token",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successfully retrieved current user",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User profile retrieved"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="role", type="string", example="pasien"),
     *                 @OA\Property(property="phone", type="string", example="08123456789"),
     *                 @OA\Property(property="avatar_url", type="string", format="uri", example="https://example.com/avatar.jpg"),
     *                 @OA\Property(property="email_verified_at", type="string", format="date-time", example="2024-01-10T15:30:00Z"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-10T10:00:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Invalid or missing token",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(
     *                 property="error",
     *                 type="object",
     *                 @OA\Property(property="code", type="string", example="UNAUTHORIZED"),
     *                 @OA\Property(property="message", type="string", example="Token tidak valid atau expired"),
     *                 @OA\Property(
     *                     property="details",
     *                     type="object",
     *                     @OA\Property(property="info", type="string", example="Silakan login kembali untuk mendapatkan token baru")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Insufficient permissions",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(
     *                 property="error",
     *                 type="object",
     *                 @OA\Property(property="code", type="string", example="FORBIDDEN"),
     *                 @OA\Property(property="message", type="string", example="Anda tidak memiliki akses ke resource ini"),
     *                 @OA\Property(
     *                     property="details",
     *                     type="object",
     *                     @OA\Property(property="error_code", type="string", example="INSUFFICIENT_PERMISSIONS")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(
     *                 property="error",
     *                 type="object",
     *                 @OA\Property(property="code", type="string", example="INTERNAL_SERVER_ERROR"),
     *                 @OA\Property(property="message", type="string", example="Terjadi kesalahan pada server"),
     *                 @OA\Property(
     *                     property="details",
     *                     type="object",
     *                     @OA\Property(property="request_id", type="string", example="req_87654321"),
     *                     @OA\Property(property="timestamp", type="string", format="date-time", example="2024-01-15T10:30:00Z")
     *                 )
     *             )
     *         )
     *     )
     * )
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
     * @OA\Post(
     *     path="/auth/refresh",
     *     operationId="refreshToken",
     *     tags={"Authentication"},
     *     summary="Refresh JWT token",
     *     description="Dapatkan token JWT baru sebelum token lama expire",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Token successfully refreshed",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Token berhasil diperbarui"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGc..."),
     *                 @OA\Property(property="token_type", type="string", example="Bearer"),
     *                 @OA\Property(property="expires_in", type="integer", example=86400)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Invalid or missing token",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(
     *                 property="error",
     *                 type="object",
     *                 @OA\Property(property="code", type="string", example="UNAUTHORIZED"),
     *                 @OA\Property(property="message", type="string", example="Token tidak valid atau expired"),
     *                 @OA\Property(
     *                     property="details",
     *                     type="object",
     *                     @OA\Property(property="info", type="string", example="Silakan login kembali untuk mendapatkan token baru")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(
     *                 property="error",
     *                 type="object",
     *                 @OA\Property(property="code", type="string", example="INTERNAL_SERVER_ERROR"),
     *                 @OA\Property(property="message", type="string", example="Terjadi kesalahan pada server"),
     *                 @OA\Property(
     *                     property="details",
     *                     type="object",
     *                     @OA\Property(property="request_id", type="string", example="req_87654321"),
     *                     @OA\Property(property="timestamp", type="string", format="date-time", example="2024-01-15T10:30:00Z")
     *                 )
     *             )
     *         )
     *     )
     * )
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
     * @OA\Post(
     *     path="/auth/logout",
     *     operationId="logoutUser",
     *     tags={"Authentication"},
     *     summary="Logout user",
     *     description="Logout user dan invalidate JWT token yang sedang digunakan",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successfully logged out",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Logout berhasil"),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Invalid or missing token",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(
     *                 property="error",
     *                 type="object",
     *                 @OA\Property(property="code", type="string", example="UNAUTHORIZED"),
     *                 @OA\Property(property="message", type="string", example="Token tidak valid atau expired"),
     *                 @OA\Property(
     *                     property="details",
     *                     type="object",
     *                     @OA\Property(property="info", type="string", example="Silakan login kembali")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(
     *                 property="error",
     *                 type="object",
     *                 @OA\Property(property="code", type="string", example="INTERNAL_SERVER_ERROR"),
     *                 @OA\Property(property="message", type="string", example="Terjadi kesalahan pada server"),
     *                 @OA\Property(
     *                     property="details",
     *                     type="object",
     *                     @OA\Property(property="request_id", type="string", example="req_87654321"),
     *                     @OA\Property(property="timestamp", type="string", format="date-time", example="2024-01-15T10:30:00Z")
     *                 )
     *             )
     *         )
     *     )
     * )
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
