<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ProfileUpdateRequest;
use App\Services\AuthService;
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
     * 
     * Fungsi ini membuat akun baru untuk pasien atau dokter.
     * Untuk pasien: Buat user + data pasien di tabel patients
     * Untuk dokter: Buat user + data dokter di tabel doctors
     * 
     * POST /api/v1/auth/register
     * 
     * Body Parameters:
     * {
     *   "name": "Ahmad Zaki",
     *   "email": "ahmad@mail.com",
     *   "password": "Password123!",
     *   "role": "pasien",
     *   
     *   // Jika role = pasien:
     *   "nik": "3215001234567890",
     *   "tgl_lahir": "2015-06-15",
     *   "jenis_kelamin": "Laki-laki",
     *   "alamat": "Jl. Raya Pasuruan No. 123",
     *   "no_telepon": "081234567890",
     *   "golongan_darah": "O",
     *   "nama_kontak_darurat": "Budi Zaki",
     *   "no_kontak_darurat": "082345678901",
     *   
     *   // Jika role = dokter:
     *   "spesialisasi": "Dokter Anak",
     *   "no_lisensi": "SIP-JE-2020-000001",
     *   "no_telepon": "081999888777"
     * }
     * 
     * Password Requirements:
     * - Minimal 8 karakter
     * - Minimal 1 huruf besar
     * - Minimal 1 huruf kecil
     * - Minimal 1 angka
     * - Minimal 1 simbol (@$!%*?&)
     * 
     * @param Request $request - HTTP request dengan data registrasi
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * Register user baru (Pasien atau Dokter)
     * 
     * Menggunakan FormRequest untuk validasi otomatis
     * dan AuthService untuk business logic.
     * 
     * POST /api/v1/auth/register
     */
    public function register(RegisterRequest $request)
    {
        // Validation happens automatically in RegisterRequest
        $result = $this->authService->register($request->validated());
        
        return $this->createdResponse(
            $result,
            'User berhasil terdaftar'
        );
    }

    /**
     * Login user dengan email dan password
     * 
     * Fungsi ini mengautentikasi user dan mengembalikan JWT token.
     * User harus aktif (is_active = true) untuk bisa login.
     * 
     * POST /api/v1/auth/login
     * 
     * Body Parameters:
     * {
     *   "email": "ahmad@mail.com",
     *   "password": "Password123!"
     * }
     * 
     * @param Request $request - Email dan password
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * Login user dengan email/NIK dan password
     * 
     * Menggunakan LoginRequest untuk validasi
     * dan AuthService untuk business logic.
     * 
     * POST /api/v1/auth/login
     */
    public function login(LoginRequest $request)
    {
        // Validation happens automatically in LoginRequest
        $data = $request->validated();
        
        $result = $this->authService->login(
            $data['email'],
            $data['password']
        );
        
        if (!$result) {
            return $this->unauthorizedResponse('Email atau password salah');
        }
        
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
     * Ambil profil user yang sedang login
     * 
     * GET /api/v1/auth/me
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
}