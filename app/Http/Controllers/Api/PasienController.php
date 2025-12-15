<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PasienRequest;
use App\Models\Pasien;
use App\Models\User;
use App\Services\PasienService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * ============================================
 * KONTROLER PASIEN - CRUD OPERATIONS
 * ============================================
 * 
 * File ini berfungsi untuk:
 * - GET /api/v1/pasien - List semua pasien (Admin only)
 * - POST /api/v1/pasien - Buat pasien baru (Admin only)
 * - GET /api/v1/pasien/{id} - Detail pasien
 * - PUT /api/v1/pasien/{id} - Update data pasien
 * - DELETE /api/v1/pasien/{id} - Delete pasien (Admin only)
 * - GET /api/v1/pasien/{id}/rekam-medis - Rekam medis pasien
 * - GET /api/v1/pasien/{id}/konsultasi - Konsultasi pasien
 * 
 * @author Aplikasi Telemedicine RSUD dr. R. Soedarsono
 * @version 2.0
 */
class PasienController extends Controller
{
    use ApiResponse;

    private PasienService $pasienService;

    public function __construct(PasienService $pasienService)
    {
        $this->pasienService = $pasienService;
    }
    /**
     * LIST - Tampilkan daftar semua pasien (Admin only)
     * 
     * GET /api/v1/pasien
     */
    public function index(Request $request)
    {
        if (!Auth::user()?->isAdmin()) {
            return $this->forbiddenResponse('Anda tidak berhak mengakses data semua pasien');
        }

        $pasien = $this->pasienService->getAllPasien([
            'per_page' => $request->get('per_page', 15),
            'search' => $request->get('search'),
            'sort' => $request->get('sort', 'created_at'),
            'order' => $request->get('order', 'desc'),
        ]);

        return $this->paginatedResponse(
            $pasien->items(),
            'Daftar pasien berhasil diambil',
            $pasien
        );
    }

    /**
     * STORE - Buat pasien baru (Admin only)
     * 
     * POST /api/v1/pasien
     * 
     * Body Parameters:
     * {
     *   "nama": "Ahmad Zaki",
     *   "email": "ahmad@example.com",
     *   "password": "SecurePass123!",
     *   "nik": "1234567890123456",
     *   "tgl_lahir": "2015-06-15",
     *   "jenis_kelamin": "Laki-laki",
     *   "alamat": "Jl. Raya Pasuruan No. 123",
     *   "no_telepon": "081234567890",
     *   "golongan_darah": "O",
     *   "alergi": "Penisilin",
     *   "nama_kontak_darurat": "Budi Zaki",
     *   "no_kontak_darurat": "082345678901"
     * }
     * 
     * @param PasienRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(PasienRequest $request)
    {
        // Authorization check - only admin can create pasien
        if (!Auth::user()?->isAdmin()) {
            return $this->forbiddenResponse('Anda tidak berhak membuat pasien baru');
        }

        // Get validated data
        $validated = $request->validated();

        // Create pasien using service
        $pasien = $this->pasienService->createPasien($validated);

        return $this->createdResponse(
            [
                'id' => $pasien->id,
                'user_id' => $pasien->user_id,
                'nama' => $pasien->pengguna->name,
                'email' => $pasien->pengguna->email,
                'nik' => $pasien->nik,
                'tgl_lahir' => $pasien->tgl_lahir,
                'jenis_kelamin' => $pasien->jenis_kelamin,
                'golongan_darah' => $pasien->golongan_darah,
                'created_at' => $pasien->created_at,
            ],
            'Pasien baru berhasil dibuat'
        );
    }

    /**
     * SHOW - Tampilkan detail pasien
     * 
     * GET /api/v1/pasien/{id}
     * 
     * @param int $id - ID Pasien
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        // Get pasien by ID
        $pasien = $this->pasienService->getPasienById($id);
        
        // Check if pasien exists
        if (!$pasien) {
            return $this->notFoundResponse('Pasien tidak ditemukan');
        }

        // Authorization check - only pasien self or admin
        $user = Auth::user();
        if ($user && $user->isPasien() && $user->id !== $pasien->user_id) {
            return $this->forbiddenResponse('Anda tidak berhak mengakses data pasien lain');
        }

        return $this->successResponse(
            [
                'id' => $pasien->id,
                'user_id' => $pasien->user_id,
                'nama' => $pasien->pengguna->name,
                'email' => $pasien->pengguna->email,
                'nik' => $pasien->nik,
                'tgl_lahir' => $pasien->tgl_lahir,
                'umur' => $pasien->umur,
                'jenis_kelamin' => $pasien->jenis_kelamin,
                'alamat' => $pasien->alamat,
                'no_telepon' => $pasien->no_telepon,
                'golongan_darah' => $pasien->golongan_darah,
                'alergi' => $pasien->alergi,
                'nama_kontak_darurat' => $pasien->nama_kontak_darurat,
                'no_kontak_darurat' => $pasien->no_kontak_darurat,
                'status_aktif' => $pasien->pengguna->is_active,
                'created_at' => $pasien->created_at,
                'updated_at' => $pasien->updated_at,
                // Statistics
                'stats' => [
                    'total_konsultasi' => $pasien->konsultasi()->count(),
                    'konsultasi_aktif' => $pasien->konsultasi()->where('status', 'aktif')->count(),
                    'konsultasi_menunggu' => $pasien->konsultasi()->where('status', 'menunggu')->count(),
                    'konsultasi_selesai' => $pasien->konsultasi()->where('status', 'selesai')->count(),
                    'total_rekam_medis' => $pasien->rekamMedis()->count(),
                ]
            ],
            'Detail pasien berhasil diambil'
        );
    }

    /**
     * UPDATE - Update data pasien
     * 
     * PUT /api/v1/pasien/{id}
     * 
     * Body Parameters:
     * {
     *   "nama": "Ahmad Zaki",
     *   "tgl_lahir": "2015-06-15",
     *   "jenis_kelamin": "Laki-laki",
     *   "alamat": "Jl. Raya Pasuruan No. 123",
     *   "no_telepon": "081234567890",
     *   "golongan_darah": "O",
     *   "alergi": "Penisilin, Aspirin",
     *   "nama_kontak_darurat": "Budi Zaki",
     *   "no_kontak_darurat": "082345678901"
     * }
     * 
     * @param int $id
     * @param PasienRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id, PasienRequest $request)
    {
        // Find pasien by ID or user_id
        $pasien = Pasien::where('id', $id)
            ->orWhere('user_id', $id)
            ->with('pengguna')
            ->first();
        
        if (!$pasien) {
            return $this->notFoundResponse('Pasien tidak ditemukan');
        }

        // Authorization check - only pasien self or admin
        $user = Auth::user();
        if ($user && $user->isPasien() && $user->id !== $pasien->user_id) {
            return $this->forbiddenResponse('Anda tidak berhak mengupdate data pasien lain');
        }

        // Get validated data
        $validated = $request->validated();

        // Update pasien using service
        $pasien = $this->pasienService->updatePasien($pasien, $validated);

        return $this->successResponse(
            [
                'id' => $pasien->id,
                'user_id' => $pasien->user_id,
                'nama' => $pasien->pengguna->name,
                'email' => $pasien->pengguna->email,
                'nik' => $pasien->nik,
                'tgl_lahir' => $pasien->tgl_lahir,
                'jenis_kelamin' => $pasien->jenis_kelamin,
                'alamat' => $pasien->alamat,
                'no_telepon' => $pasien->no_telepon,
                'golongan_darah' => $pasien->golongan_darah,
                'alergi' => $pasien->alergi,
                'nama_kontak_darurat' => $pasien->nama_kontak_darurat,
                'no_kontak_darurat' => $pasien->no_kontak_darurat,
                'updated_at' => $pasien->updated_at,
            ],
            'Data pasien berhasil diupdate'
        );
    }

    /**
     * DELETE - Hapus data pasien (Admin only)
     * 
     * DELETE /api/v1/pasien/{id}
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        // Authorization check - only admin
        if (!Auth::user()?->isAdmin()) {
            return $this->forbiddenResponse('Anda tidak berhak menghapus pasien');
        }

        // Get pasien
        $pasien = Pasien::find($id);
        
        if (!$pasien) {
            return $this->notFoundResponse('Pasien tidak ditemukan');
        }

        // Check if pasien has active consultations
        if ($pasien->konsultasi()->where('status', 'aktif')->count() > 0) {
            return $this->errorResponse(
                'Tidak bisa menghapus pasien dengan konsultasi aktif',
                400
            );
        }

        // Delete pasien using service
        $this->pasienService->deletePasien($pasien);

        return $this->successResponse(
            null,
            'Pasien berhasil dihapus'
        );
    }

    /**
     * REKAM MEDIS - Ambil rekam medis pasien
     * 
     * GET /api/v1/pasien/{id}/rekam-medis
     * 
     * Query Parameters:
     * - tipe: Filter berdasarkan tipe record
     * - sumber: SIMRS atau LOKAL
     * - per_page: Jumlah per halaman (default: 15)
     * 
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function rekamMedis($id, Request $request)
    {
        // Get pasien
        $pasien = Pasien::find($id);
        
        if (!$pasien) {
            return $this->notFoundResponse('Pasien tidak ditemukan');
        }

        // Authorization check - only pasien self or admin
        $user = Auth::user();
        if ($user && $user->isPasien() && $user->id !== $pasien->user_id) {
            return $this->forbiddenResponse('Anda tidak berhak mengakses rekam medis pasien lain');
        }

        // Get medical records using service
        $rekamMedis = $this->pasienService->getPasienMedicalRecords($pasien, [
            'per_page' => $request->get('per_page', 15),
        ]);

        return $this->paginatedResponse(
            $rekamMedis,
            'Rekam medis pasien berhasil diambil'
        );
    }

    /**
     * KONSULTASI - Ambil konsultasi pasien
     * 
     * GET /api/v1/pasien/{id}/konsultasi
     * 
     * Query Parameters:
     * - status: Filter by status (menunggu, aktif, selesai, dibatalkan)
     * - per_page: Jumlah per halaman (default: 15)
     * 
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function konsultasi($id, Request $request)
    {
        // Get pasien
        $pasien = Pasien::find($id);
        
        if (!$pasien) {
            return $this->notFoundResponse('Pasien tidak ditemukan');
        }

        // Authorization check - only pasien self or admin
        $user = Auth::user();
        if ($user && $user->isPasien() && $user->id !== $pasien->user_id) {
            return $this->forbiddenResponse('Anda tidak berhak mengakses konsultasi pasien lain');
        }

        // Get consultations using service
        $konsultasi = $this->pasienService->getPasienConsultations($pasien, [
            'status' => $request->get('status'),
            'per_page' => $request->get('per_page', 15),
        ]);

        return $this->paginatedResponse(
            $konsultasi,
            'Konsultasi pasien berhasil diambil'
        );
    }
}