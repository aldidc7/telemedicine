<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DokterRequest;
use App\Models\Dokter;
use App\Models\Pasien;
use App\Services\DokterService;
use App\Services\DoctorSearchService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * ============================================
 * KONTROLER DOKTER - CRUD OPERATIONS
 * ============================================
 * 
 * File ini berfungsi untuk:
 * - GET /api/v1/dokter - List semua dokter
 * - POST /api/v1/dokter - Buat dokter baru (Admin only)
 * - GET /api/v1/dokter/{id} - Detail dokter
 * - PUT /api/v1/dokter/{id} - Update dokter
 * - DELETE /api/v1/dokter/{id} - Delete dokter (Admin only)
 * - PUT /api/v1/dokter/{id}/ketersediaan - Update availability
 * 
 * @author Aplikasi Telemedicine RSUD dr. R. Soedarsono
 * @version 2.0
 */
class DokterController extends Controller
{
    use ApiResponse;

    protected DokterService $dokterService;

    /**
     * Constructor - Inject DokterService
     * 
     * @param DokterService $dokterService
     */
    public function __construct(DokterService $dokterService)
    {
        $this->dokterService = $dokterService;
    }
    /**
     * LIST - Tampilkan daftar semua dokter
     * 
     * GET /api/v1/dokter
     * 
     * Query Parameters:
     * - per_page: Jumlah data per halaman (default: 15)
     * - tersedia: Filter dokter yang tersedia (true/false)
     * - spesialisasi: Filter by specialty
     * - search: Cari berdasarkan nama
     * - sort: Field untuk sorting (default: created_at)
     * - order: asc atau desc
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Get user role
        $user = Auth::user();
        $isAdmin = $user?->isAdmin();
        
        $filters = [
            'per_page' => $request->get('per_page', 15),
            'search' => $request->get('search'),
            'tersedia' => $request->get('tersedia'),
            'spesialisasi' => $request->get('spesialisasi'),
            'sort' => $request->get('sort', 'created_at'),
            'order' => $request->get('order', 'desc'),
        ];

        // Non-admin users (pasien/dokter) hanya bisa lihat dokter yang verified dan tersedia
        if (!$isAdmin) {
            $filters['is_verified'] = true;  // CRITICAL: Block unverified doctors
            $filters['tersedia'] = true;
        }

        $dokter = $this->dokterService->getAllDokter($filters);

        return $this->paginatedResponse(
            $dokter,
            'Daftar dokter berhasil diambil'
        );
    }

    /**
     * STORE - Buat dokter baru (Admin only)
     * 
     * POST /api/v1/dokter
     * 
     * Body Parameters:
     * {
     *   "nama": "Dr. Budi Santoso",
     *   "email": "budi@example.com",
     *   "password": "SecurePass123!",
     *   "spesialisasi": "Umum",
     *   "no_lisensi": "12345678",
     *   "no_telepon": "081234567890",
     *   "maks_konsultasi_simultan": 5
     * }
     * 
     * @param DokterRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(DokterRequest $request)
    {
        // Authorization check - hanya admin yang bisa buat dokter
        if (!Auth::user()?->isAdmin()) {
            return $this->forbiddenResponse('Anda tidak berhak membuat dokter baru');
        }

        $dokter = $this->dokterService->createDokter($request->validated());

        return $this->createdResponse(
            [
                'id' => $dokter->id,
                'user_id' => $dokter->user_id,
                'nama' => $dokter->user->name,
                'email' => $dokter->user->email,
                'specialization' => $dokter->specialization,
                'license_number' => $dokter->license_number,
                'phone_number' => $dokter->phone_number,
                'is_available' => $dokter->is_available,
                'max_concurrent_consultations' => $dokter->max_concurrent_consultations,
                'created_at' => $dokter->created_at,
            ],
            'Dokter baru berhasil dibuat'
        );
    }

    /**
     * GET BY USER ID - Dapatkan dokter berdasarkan user_id
     * 
     * GET /api/v1/dokter/user/{user_id}
     * 
     * @param int $user_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByUserId($user_id)
    {
        $dokter = $this->dokterService->getDokterByUserId($user_id);

        if (!$dokter) {
            return $this->notFoundResponse('Dokter tidak ditemukan untuk user ini');
        }

        return $this->successResponse(
            [
                'id' => $dokter->id,
                'user_id' => $dokter->user_id,
                'name' => $dokter->user->name,
                'email' => $dokter->user->email,
                'specialization' => $dokter->specialization,
                'license_number' => $dokter->license_number,
                'phone_number' => $dokter->phone_number,
                'is_available' => $dokter->is_available,
                'max_concurrent_consultations' => $dokter->max_concurrent_consultations,
                'profile_photo' => $dokter->profile_photo,
                'address' => $dokter->address,
                'place_of_birth' => $dokter->place_of_birth,
                'birthplace_city' => $dokter->birthplace_city,
                'marital_status' => $dokter->marital_status,
                'gender' => $dokter->gender,
                'blood_type' => $dokter->blood_type,
                'ethnicity' => $dokter->ethnicity,
            ],
            'Data dokter berhasil diambil'
        );
    }

    /**
     * SHOW - Tampilkan detail dokter
     * 
     * GET /api/v1/dokter/{id}
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $dokter = $this->dokterService->getDokterById($id);

            if (!$dokter) {
                return $this->notFoundResponse('Dokter tidak ditemukan');
            }

            $jmlKonsultasiAktif = $dokter->konsultasi()
                ->where('status', 'aktif')
                ->count();

            // Get rating statistics
            $ratingStats = \App\Models\Rating::where('dokter_id', $dokter->id)
                ->selectRaw('COUNT(*) as total_reviews, AVG(rating) as average_rating')
                ->first();

            return $this->successResponse(
                [
                    'id' => $dokter->id,
                    'user_id' => $dokter->user_id,
                    'nama' => $dokter->user->name,
                    'email' => $dokter->user->email,
                    'specialization' => $dokter->specialization,
                    'license_number' => $dokter->license_number,
                    'phone_number' => $dokter->phone_number,
                    'is_available' => $dokter->is_available,
                    'tersedia' => $dokter->tersedia ?? $dokter->is_available,
                    'max_concurrent_consultations' => $dokter->max_concurrent_consultations,
                    'konsultasi_aktif_sekarang' => $jmlKonsultasiAktif,
                    'status_aktif' => $dokter->user->is_active,
                    'rating' => round($ratingStats->average_rating ?? 0, 2),
                    'total_reviews' => (int)($ratingStats->total_reviews ?? 0),
                    'review_count' => (int)($ratingStats->total_reviews ?? 0),
                    'created_at' => $dokter->created_at,
                    'updated_at' => $dokter->updated_at,
                    // Statistics
                    'stats' => [
                        'total_konsultasi' => $dokter->konsultasi()->count(),
                        'konsultasi_menunggu' => $dokter->konsultasi()->where('status', 'menunggu')->count(),
                        'konsultasi_aktif' => $jmlKonsultasiAktif,
                        'konsultasi_selesai' => $dokter->konsultasi()->where('status', 'selesai')->count(),
                        'konsultasi_selesai_bulan_ini' => $dokter->konsultasi()
                            ->where('status', 'selesai')
                            ->whereMonth('waktu_selesai', now()->month)
                            ->count(),
                    ]
                ],
                'Detail dokter berhasil diambil'
            );
        } catch (\Exception $e) {
            \Log::error('Error in DokterController.show: ' . $e->getMessage());
            return $this->errorResponse('Gagal mengambil detail dokter: ' . $e->getMessage(), 500);
        }
    }

    /**
     * UPDATE - Update data dokter
     * 
     * PUT /api/v1/dokter/{id}
     * 
     * Body Parameters:
     * {
     *   "nama": "Dr. Budi Santoso",
     *   "no_telepon": "081234567890",
     *   "maks_konsultasi_simultan": 5
     * }
     * 
     * @param int $id
     * @param DokterRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id, DokterRequest $request)
    {
        try {
            // Debug logging - RIGHT AFTER REQUEST VALIDATION
            \Log::info('Dokter update - AFTER DokterRequest validation', [
                'dokter_id' => $id,
                'request_all' => $request->all(),
                'has_file' => $request->hasFile('profile_photo'),
                'request_keys' => array_keys($request->all()),
                'validated_keys' => array_keys($request->validated()),
            ]);
            
            // Try to get file directly
            if ($request->hasFile('profile_photo')) {
                $file = $request->file('profile_photo');
                \Log::info('Profile photo file found', [
                    'name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime' => $file->getMimeType(),
                ]);
            } else {
                \Log::warning('No profile_photo file found in request');
            }
            
            $dokter = Dokter::find($id);

            if (!$dokter) {
                return $this->notFoundResponse('Dokter tidak ditemukan');
            }

            // Authorization check - dokter hanya bisa update diri sendiri, admin bisa update semua
            $user = Auth::user();
            if ($user && $user->isDokter() && $user->id !== $dokter->user_id) {
                return $this->forbiddenResponse('Anda tidak berhak mengupdate data dokter lain');
            }

            $dokter = $this->dokterService->updateDokter($dokter, $request->validated());

            return $this->successResponse(
                [
                    'id' => $dokter->id,
                    'name' => $dokter->user->name,
                    'email' => $dokter->user->email,
                    'phone_number' => $dokter->phone_number,
                    'specialization' => $dokter->specialization,
                    'license_number' => $dokter->license_number,
                    'max_concurrent_consultations' => $dokter->max_concurrent_consultations,
                    'is_available' => $dokter->is_available,
                    'profile_photo' => $dokter->profile_photo,
                    'address' => $dokter->address,
                    'place_of_birth' => $dokter->place_of_birth,
                    'birthplace_city' => $dokter->birthplace_city,
                    'marital_status' => $dokter->marital_status,
                    'gender' => $dokter->gender,
                    'blood_type' => $dokter->blood_type,
                    'ethnicity' => $dokter->ethnicity,
                    'updated_at' => $dokter->updated_at,
                ],
                'Data dokter berhasil diupdate'
            );
        } catch (\Throwable $e) {
            \Log::error('Error updating dokter profile: ' . $e->getMessage(), [
                'dokter_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->errorResponse(
                'Gagal mengupdate data dokter: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * DELETE - Hapus data dokter (Admin only)
     * 
     * DELETE /api/v1/dokter/{id}
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        // Authorization check - hanya admin
        if (!Auth::user()?->isAdmin()) {
            return $this->forbiddenResponse('Anda tidak berhak menghapus dokter');
        }

        $dokter = Dokter::find($id);

        if (!$dokter) {
            return $this->notFoundResponse('Dokter tidak ditemukan');
        }

        $this->dokterService->deleteDokter($dokter);

        return $this->successResponse(null, 'Dokter berhasil dihapus');
    }

    /**
     * SYNC TO PATIENT - Sinkronisasi data dokter ke profil pasien
     * 
     * POST /api/v1/dokter/{id}/sync-patient
     * 
     * Hanya untuk dokter yang juga ingin menjadi pasien di sistem
     * 
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function syncToPatient($id, Request $request)
    {
        $dokter = Dokter::find($id);

        if (!$dokter) {
            return $this->notFoundResponse('Dokter tidak ditemukan');
        }

        // Authorization - hanya dokter sendiri atau admin
        $user = Auth::user();
        if ($user && $user->isDokter() && $user->id !== $dokter->user_id) {
            return $this->forbiddenResponse('Anda tidak berhak sinkronisasi data dokter lain');
        }

        // Validasi input
        $validated = $request->validate([
            'patient_ids' => 'nullable|array',
            'patient_ids.*' => 'integer|exists:patients,id',
            'gender' => 'nullable|string|in:laki-laki,perempuan',
            'birthplace_city' => 'nullable|string|max:100',
            'blood_type' => 'nullable|string|in:O,A,B,AB',
            'address' => 'nullable|string|max:500',
            'ethnicity' => 'nullable|string|max:100',
        ]);

        // Cek apakah pasien sudah ada
        $pasien = Pasien::where('user_id', $dokter->user_id)->first();

        if (!$pasien) {
            // Buat pasien baru dengan NIK dari user_id (placeholder)
            $pasienData = [
                'user_id' => $dokter->user_id,
                'nik' => 'DOK-' . $dokter->user_id . '-' . now()->timestamp,
                'date_of_birth' => now()->subYears(30)->format('Y-m-d'), // Default 30 tahun lalu
                'phone_number' => $dokter->phone_number ?? '08000000000',
            ];

            // Add optional fields if provided
            if (isset($validated['gender'])) $pasienData['gender'] = $validated['gender'];
            if (isset($validated['birthplace_city'])) $pasienData['birthplace_city'] = $validated['birthplace_city'];
            if (isset($validated['blood_type'])) $pasienData['blood_type'] = $validated['blood_type'];
            if (isset($validated['address'])) $pasienData['address'] = $validated['address'];
            if (isset($validated['ethnicity'])) $pasienData['ethnicity'] = $validated['ethnicity'];

            $pasien = Pasien::create($pasienData);
        } else {
            // Update pasien yang sudah ada
            $updateData = [];
            if (isset($validated['gender'])) $updateData['gender'] = $validated['gender'];
            if (isset($validated['birthplace_city'])) $updateData['birthplace_city'] = $validated['birthplace_city'];
            if (isset($validated['blood_type'])) $updateData['blood_type'] = $validated['blood_type'];
            if (isset($validated['address'])) $updateData['address'] = $validated['address'];
            if (isset($validated['ethnicity'])) $updateData['ethnicity'] = $validated['ethnicity'];
            
            if (!empty($updateData)) {
                $pasien->update($updateData);
            }
        }

        // Update status di dokter
        $dokter->update(['patient_synced' => true]);

        return $this->successResponse(
            [
                'pasien_id' => $pasien->id,
                'user_id' => $pasien->user_id,
                'patient_synced' => $dokter->patient_synced,
            ],
            'Data pasien berhasil disinkronisasi'
        );
    }

    /**
     * KETERSEDIAAN - Update status ketersediaan dokter
     * 
     * PUT /api/v1/dokter/{id}/ketersediaan
     * 
     * Body:
     * {
     *   "tersedia": true/false
     * }
     * 
     * Hanya dokter sendiri yang bisa update ketersediaan mereka sendiri
     * 
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateKetersediaan($id, Request $request)
    {
        try {
            $dokter = Dokter::find($id);

            if (!$dokter) {
                return $this->notFoundResponse('Dokter tidak ditemukan');
            }

            // Authorization check - dokter hanya bisa update diri sendiri
            $user = Auth::user();
            if ($user && $user->isDokter() && $user->id !== $dokter->user_id) {
                return $this->forbiddenResponse('Anda tidak berhak mengupdate ketersediaan dokter lain');
            }

            // Validation - accept both 'tersedia' and 'is_available' from frontend
            $validated = $request->validate([
                'tersedia' => 'nullable|boolean',
                'is_available' => 'nullable|boolean',
            ]);

            // Get the value from either field
            $newStatus = $validated['tersedia'] ?? $validated['is_available'] ?? false;

            $dokter = $this->dokterService->updateKetersediaan($dokter, ['tersedia' => $newStatus]);

            return $this->successResponse(
                [
                    'id' => $dokter->id,
                    'nama' => $dokter->user->name,
                    'tersedia' => $dokter->is_available,
                    'is_available' => $dokter->is_available,
                    'status_text' => $dokter->is_available ? 'Online & Tersedia' : 'Offline / Sedang Istirahat',
                    'updated_at' => $dokter->updated_at,
                ],
                'Status ketersediaan berhasil diupdate'
            );
        } catch (\Exception $e) {
            \Log::error('Error updating dokter availability: ' . $e->getMessage());
            return $this->errorResponse('Gagal mengupdate ketersediaan: ' . $e->getMessage(), 500);
        }
    }

    /**
     * GET - List dokter terverifikasi untuk pasien
     * 
     * Hanya menampilkan dokter yang sudah diverifikasi oleh admin
     * Digunakan untuk pasien mencari dan memilih dokter untuk konsultasi
     * 
     * GET /api/v1/dokter/public/terverifikasi
     * 
     * Query Parameters:
     * - tersedia: Filter dokter yang tersedia (true/false)
     * - spesialisasi: Filter by specialty
     * - search: Cari berdasarkan nama
     * - per_page: Jumlah data per halaman (default: 15)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifiedDoctors(Request $request)
    {
        // Use advanced search service
        $doctors = DoctorSearchService::search($request->all());

        return $this->paginatedResponse(
            $doctors,
            'Daftar dokter berhasil diambil',
            [
                'available_count' => DoctorSearchService::getAvailableCount(),
                'specializations' => DoctorSearchService::getSpecializations(),
                'stats' => DoctorSearchService::getSpecializationStats(),
            ]
        );
    }

    /**
     * Search doctors dengan advanced filters
     * 
     * GET /api/v1/dokter/search/advanced
     * 
     * Query Parameters:
     * - q: search keyword (name, specialization)
     * - specialization: filter by specialization
     * - available: filter by availability (true/false)
     * - min_rating: filter by minimum rating
     * - verified_only: hanya dokter verified (true/false)
     * - page: pagination page
     * - per_page: items per page
     * - sort: sort by field (name, rating, availability)
     * - order: asc or desc
     */
    public function search(Request $request)
    {
        $doctors = DoctorSearchService::search($request->all());

        return $this->paginatedResponse(
            $doctors,
            'Pencarian dokter berhasil',
            [
                'available_count' => DoctorSearchService::getAvailableCount(),
                'specializations' => DoctorSearchService::getSpecializations(),
            ]
        );
    }

    /**
     * Get doctor detail dengan ratings
     * 
     * GET /api/v1/dokter/{id}/detail
     */
    public function detail($id)
    {
        $doctor = DoctorSearchService::getDoctorDetail((int) $id);

        if (!$doctor) {
            return $this->notFoundResponse('Dokter tidak ditemukan atau belum terverifikasi');
        }

        return $this->successResponse($doctor, 'Detail dokter berhasil diambil');
    }

    /**
     * Get top-rated doctors
     * 
     * GET /api/v1/dokter/top-rated
     */
    public function topRated(Request $request)
    {
        $limit = min((int) ($request->get('limit') ?? 10), 50);
        $doctors = DoctorSearchService::getTopRated($limit);

        return $this->successResponse([
            'doctors' => $doctors,
            'total' => count($doctors),
        ], 'Dokter dengan rating tertinggi berhasil diambil');
    }

    /**
     * Get doctor specializations
     * 
     * GET /api/v1/dokter/specializations
     */
    public function specializations()
    {
        $specializations = DoctorSearchService::getSpecializations();
        $stats = DoctorSearchService::getSpecializationStats();

        return $this->successResponse([
            'specializations' => $specializations,
            'stats' => $stats,
        ], 'Daftar spesialisasi dokter berhasil diambil');
    }
}
