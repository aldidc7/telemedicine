<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Pasien;
use App\Models\Dokter;
use App\Models\Admin;
use App\Models\Konsultasi;
use App\Models\PesanChat;
use App\Models\ActivityLog;
use App\Models\SystemLog;
use App\Services\DashboardCacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

/**
 * ============================================
 * KONTROLER ADMIN - DASHBOARD & MANAGEMENT
 * ============================================
 * 
 * File ini berfungsi untuk:
 * - GET /api/v1/admin/dashboard - Dashboard stats
 * - GET /api/v1/admin/pengguna - List semua pengguna
 * - GET /api/v1/admin/pengguna/{id} - Detail pengguna
 * - PUT /api/v1/admin/pengguna/{id} - Update pengguna
 * - PUT /api/v1/admin/pengguna/{id}/nonaktif - Nonaktifkan user
 * - PUT /api/v1/admin/pengguna/{id}/aktif - Aktifkan user
 * - DELETE /api/v1/admin/pengguna/{id} - Hapus pengguna
 * - GET /api/v1/admin/log-aktivitas - Activity logs
 * - GET /api/v1/admin/statistik - Statistik sistem
 * 
 * @author Aplikasi Telemedicine RSUD dr. R. Soedarsono
 * @version 2.0
 */
class AdminController extends Controller
{
    /**
     * Get authenticated user with proper type hint
     * 
     * @return User|null
     */
    protected function getAuthUser(): ?User
    {
        return Auth::user();
    }
    /**
     * DASHBOARD - Tampilkan dashboard admin
     * 
     * GET /api/v1/admin/dashboard
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function dashboard()
    {
        try {
            $user = $this->getAuthUser();

            // Check if admin
            if (!$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'pesan' => 'Anda bukan admin',
                ], 403);
            }

            // Use aggregation queries to reduce database hits from 15+ to 3-4
            $stats = \DB::table('patients')->selectRaw('count(*) as total')->first();
            $totalPasien = $stats?->total ?? 0;

            $stats = \DB::table('doctors')->selectRaw('count(*) as total')->first();
            $totalDokter = $stats?->total ?? 0;

            // Aggregate all Konsultasi counts in one query
            $konsultasiStats = \DB::table('consultations')
                ->selectRaw("
                    count(*) as total,
                    sum(case when status = 'active' then 1 else 0 end) as aktif,
                    sum(case when status = 'pending' then 1 else 0 end) as menunggu,
                    sum(case when status = 'closed' then 1 else 0 end) as selesai,
                    sum(case when status = 'cancelled' then 1 else 0 end) as batalkan
                ")
                ->first();

            $totalKonsultasi = $konsultasiStats?->total ?? 0;
            $konsultasiAktif = $konsultasiStats?->aktif ?? 0;
            $konsultasiMenunggu = $konsultasiStats?->menunggu ?? 0;
            $konsultasiSelesai = $konsultasiStats?->selesai ?? 0;
            $konsultasiBatalkan = $konsultasiStats?->batalkan ?? 0;

            $stats = \DB::table('admins')->selectRaw('count(*) as total')->first();
            $totalAdmin = $stats?->total ?? 0;

            // ===== MONTHLY STATS =====
            $bulanIni = now()->month;
            $tahunIni = now()->year;

            $monthlyStats = \DB::table('consultations')
                ->selectRaw("
                    sum(case when month(created_at) = ? and year(created_at) = ? then 1 else 0 end) as bulanIni,
                    sum(case when status = 'closed' and month(end_time) = ? and year(end_time) = ? then 1 else 0 end) as selesaibulanini
                ")
                ->setBindings([$bulanIni, $tahunIni, $bulanIni, $tahunIni])
                ->first();

            $konsultasiBulanIni = $monthlyStats?->bulanini ?? 0;
            $konsultasiSelesaiBulanIni = $monthlyStats?->selesaibulanini ?? 0;

            $pasienStats = \DB::table('patients')
                ->whereMonth('created_at', $bulanIni)
                ->whereYear('created_at', $tahunIni)
                ->count();
            $pasienBaru = $pasienStats;

            // ===== DOCTOR AVAILABILITY =====
            $dokterStats = \DB::table('doctors')
                ->selectRaw("
                    sum(case when is_available = true then 1 else 0 end) as tersedia,
                    sum(case when is_available = false then 1 else 0 end) as tidaktersedia
                ")
                ->first();

            $dokterTersedia = $dokterStats?->tersedia ?? 0;
            $dokterTidakTersedia = $dokterStats?->tidakttersedia ?? 0;

            // ===== USER ACTIVITY =====
            $userStats = \DB::table('users')
                ->selectRaw("
                    sum(case when is_active = true then 1 else 0 end) as aktif,
                    sum(case when is_active = false then 1 else 0 end) as nonaktif
                ")
                ->first();

            $userAktif = $userStats?->aktif ?? 0;
            $userNonaktif = $userStats?->nonaktif ?? 0;

            // ===== CONSULTATION METRICS =====
            $rataRataDurasiSelesai = Konsultasi::where('status', 'closed')
                ->whereNotNull('start_time')
                ->whereNotNull('end_time')
                ->get()
                ->avg(function ($k) {
                    if ($k->start_time && $k->end_time) {
                        return $k->start_time->diffInMinutes($k->end_time);
                    }
                    return 0;
                });

            // ===== CONSULTATION BY SPECIALTY =====
            $konsultasiPerSpesialisasi = [];
            try {
                $konsultasiPerSpesialisasi = Konsultasi::where('status', 'closed')
                    ->with('dokter')
                    ->get()
                    ->groupBy(function ($k) {
                        return $k->dokter?->specialization ?? 'Tidak Ada';
                    })
                    ->map(function ($group) {
                        return count($group);
                    })
                    ->toArray();
            } catch (\Exception $e) {
                \Log::error('Error calculating consultation by specialty: ' . $e->getMessage());
                $konsultasiPerSpesialisasi = [];
            }

            // ===== RECENT CONSULTATIONS =====
            $konsultasiTerbaru = [];
            try {
                $konsultasiTerbaru = Konsultasi::with('pasien.user', 'dokter.user')
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get()
                    ->map(function ($k) {
                        return [
                            'id' => $k->id,
                            'pasien' => $k->pasien?->user?->name ?? 'N/A',
                            'dokter' => $k->dokter?->user?->name ?? 'Belum ditugaskan',
                            'jenis_keluhan' => $k->complaint_type ?? '',
                            'status' => $k->status,
                            'created_at' => $k->created_at?->toIso8601String() ?? null,
                        ];
                    })
                    ->toArray();
            } catch (\Exception $e) {
                \Log::error('Error fetching recent consultations: ' . $e->getMessage());
                $konsultasiTerbaru = [];
            }

            // ===== RECENT ACTIVITIES =====
            $aktivitasTerbaru = [];
            try {
                $aktivitasTerbaru = ActivityLog::with('user')
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->get()
                    ->map(function ($a) {
                        return [
                            'id' => $a->id,
                            'user' => $a->user?->name ?? 'Unknown',
                            'action' => $a->action ?? '',
                            'description' => $a->description ?? '',
                            'ip_address' => $a->ip_address ?? '',
                            'created_at' => $a->created_at?->toIso8601String() ?? null,
                        ];
                    })
                    ->toArray();
            } catch (\Exception $e) {
                \Log::error('Error fetching activity logs: ' . $e->getMessage());
                $aktivitasTerbaru = [];
            }

            // ===== SYSTEM HEALTH =====
            $totalPesan = PesanChat::count();
            $totalRekamMedis = 0;
            try {
                $totalRekamMedis = \App\Models\RekamMedis::count();
            } catch (\Exception $e) {
                \Log::error('Error counting medical records: ' . $e->getMessage());
            }
            $totalActivityLogs = ActivityLog::count();

            return response()->json([
                'success' => true,
                'pesan' => 'Dashboard admin berhasil diambil',
                'data' => [
                    // Overview section
                    'overview' => [
                        'timestamp' => now()->toIso8601String(),
                        'periode' => now()->format('F Y'),
                    ],

                    // User Statistics
                    'user_stats' => [
                        'total_pasien' => $totalPasien,
                        'total_dokter' => $totalDokter,
                        'total_admin' => $totalAdmin,
                        'total_users' => $totalPasien + $totalDokter + $totalAdmin,
                        'user_aktif' => $userAktif,
                        'user_nonaktif' => $userNonaktif,
                    ],

                    // Consultation Statistics
                    'consultation_stats' => [
                        'total_konsultasi' => $totalKonsultasi,
                        'konsultasi_aktif' => $konsultasiAktif,
                        'konsultasi_menunggu' => $konsultasiMenunggu,
                        'konsultasi_selesai' => $konsultasiSelesai,
                        'konsultasi_dibatalkan' => $konsultasiBatalkan,
                        'rata_rata_durasi_menit' => round($rataRataDurasiSelesai, 2),
                    ],

                    // Monthly Statistics
                    'monthly_stats' => [
                        'bulan' => now()->format('F Y'),
                        'konsultasi_bulan_ini' => $konsultasiBulanIni,
                        'pasien_baru' => $pasienBaru,
                        'konsultasi_selesai' => $konsultasiSelesaiBulanIni,
                    ],

                    // Doctor Statistics
                    'doctor_stats' => [
                        'dokter_tersedia' => $dokterTersedia,
                        'dokter_tidak_tersedia' => $dokterTidakTersedia,
                        'total_dokter' => $totalDokter,
                        'persentase_tersedia' => $totalDokter > 0 
                            ? round(($dokterTersedia / $totalDokter) * 100, 2)
                            : 0,
                    ],

                    // Consultation by Specialty
                    'consultation_by_specialty' => $konsultasiPerSpesialisasi,

                    // System Health
                    'system_health' => [
                        'total_messages' => $totalPesan,
                        'total_medical_records' => $totalRekamMedis,
                        'total_activity_logs' => $totalActivityLogs,
                    ],

                    // Recent Data
                    'recent_consultations' => $konsultasiTerbaru,
                    'recent_activities' => $aktivitasTerbaru,
                ],
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Dashboard error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'pesan' => 'Error mengambil dashboard',
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
    }

    /**
     * LIST PENGGUNA - Tampilkan daftar semua pengguna
     * 
     * GET /api/v1/admin/pengguna
     * 
     * Query Parameters:
     * - role: Filter by role (pasien, dokter, admin)
     * - is_active: Filter by status (true/false)
     * - search: Cari berdasarkan nama atau email
     * - per_page: Jumlah data per halaman (default: 15)
     * - sort: Field untuk sorting (default: created_at)
     * - order: asc atau desc (default: desc)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function pengguna(Request $request)
    {
        try {
            $user = $this->getAuthUser();

            // Check if admin
            if (!$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'pesan' => 'Anda bukan admin',
                ], 403);
            }

            $role = $request->get('role');
            $isActive = $request->get('is_active');
            $search = $request->get('search');
            $perPage = $request->get('per_page', 15);
            $sort = $request->get('sort', 'created_at');
            $order = $request->get('order', 'desc');

            $query = User::query();

            // Role filter
            if ($role) {
                $query->where('role', $role);
            }

            // Status filter
            if ($isActive !== null) {
                $query->where('is_active', $isActive === 'true' || $isActive === '1');
            }

            // Search filter
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            // Sort
            $query->orderBy($sort, $order);

            // Paginate
            $pengguna = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'pesan' => 'Daftar pengguna berhasil diambil',
                'data' => $pengguna->items(),
                'pagination' => [
                    'total' => $pengguna->total(),
                    'per_page' => $pengguna->perPage(),
                    'current_page' => $pengguna->currentPage(),
                    'last_page' => $pengguna->lastPage(),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'pesan' => 'Error mengambil data pengguna',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * SHOW PENGGUNA - Tampilkan detail pengguna
     * 
     * GET /api/v1/admin/pengguna/{id}
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function showPengguna($id)
    {
        try {
            $user = $this->getAuthUser();

            // Check if admin
            if (!$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'pesan' => 'Anda bukan admin',
                ], 403);
            }

            $pengguna = User::findOrFail($id);

            $data = [
                'id' => $pengguna->id,
                'name' => $pengguna->name,
                'email' => $pengguna->email,
                'role' => $pengguna->role,
                'is_active' => $pengguna->is_active,
                'last_login_at' => $pengguna->last_login_at,
                'created_at' => $pengguna->created_at,
                'updated_at' => $pengguna->updated_at,
            ];

            // Add profile data based on role
            if ($pengguna->isPasien()) {
                $pasien = $pengguna->pasien;
                $data['profile'] = [
                    'type' => 'pasien',
                    'nik' => $pasien->nik,
                    'tgl_lahir' => $pasien->tgl_lahir,
                    'umur' => $pasien->umur,
                    'jenis_kelamin' => $pasien->jenis_kelamin,
                    'alamat' => $pasien->alamat,
                    'no_telepon' => $pasien->no_telepon,
                    'golongan_darah' => $pasien->golongan_darah,
                ];
                $data['stats'] = [
                    'total_konsultasi' => $pasien->konsultasi()->count(),
                    'konsultasi_aktif' => $pasien->konsultasi()->where('status', 'aktif')->count(),
                    'konsultasi_selesai' => $pasien->konsultasi()->where('status', 'selesai')->count(),
                    'total_rekam_medis' => $pasien->rekamMedis()->count(),
                ];
            } elseif ($pengguna->isDokter()) {
                $dokter = $pengguna->dokter;
                $data['profile'] = [
                    'type' => 'dokter',
                    'specialization' => $dokter->specialization,
                    'license_number' => $dokter->license_number,
                    'phone_number' => $dokter->phone_number,
                    'is_available' => $dokter->is_available,
                    'max_concurrent_consultations' => $dokter->max_concurrent_consultations,
                ];
                $data['stats'] = [
                    'total_konsultasi' => $dokter->konsultasi()->count(),
                    'konsultasi_aktif' => $dokter->konsultasi()->where('status', 'active')->count(),
                    'konsultasi_selesai' => $dokter->konsultasi()->where('status', 'closed')->count(),
                ];
            } elseif ($pengguna->isAdmin()) {
                $admin = $pengguna->admin;
                $data['profile'] = [
                    'type' => 'admin',
                    'tingkat_izin' => $admin->tingkat_izin,
                    'catatan' => $admin->catatan,
                ];
            }

            return response()->json([
                'success' => true,
                'pesan' => 'Detail pengguna berhasil diambil',
                'data' => $data,
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'pesan' => 'Pengguna tidak ditemukan',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'pesan' => 'Error mengambil detail pengguna',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * UPDATE PENGGUNA - Update data pengguna
     * 
     * PUT /api/v1/admin/pengguna/{id}
     * 
     * Body:
     * {
     *   "name": "Nama Baru",
     *   "email": "email@baru.com"
     * }
     * 
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePengguna($id, Request $request)
    {
        try {
            $user = $this->getAuthUser();

            // Check if admin
            if (!$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'pesan' => 'Anda bukan admin',
                ], 403);
            }

            $pengguna = User::findOrFail($id);

            // ✅ FIX: Better validation dengan constraint lebih ketat
            $validated = $request->validate([
                'name' => 'nullable|string|max:255|min:3',
                'email' => 'nullable|email|unique:users,email,' . $id,
            ]);

            // Update
            $pengguna->update($validated);

            return response()->json([
                'success' => true,
                'pesan' => 'Data pengguna berhasil diupdate',
                'data' => [
                    'id' => $pengguna->id,
                    'name' => $pengguna->name,
                    'email' => $pengguna->email,
                    'role' => $pengguna->role,
                    'updated_at' => $pengguna->updated_at,
                ],
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'pesan' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'pesan' => 'Pengguna tidak ditemukan',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'pesan' => 'Error mengupdate pengguna',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * NONAKTIFKAN PENGGUNA - Nonaktifkan user account
     * 
     * PUT /api/v1/admin/pengguna/{id}/nonaktif
     * 
     * Body (optional):
     * {
     *   "alasan": "Melanggar kebijakan"
     * }
     * 
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function nonaktifkanPengguna($id, Request $request)
    {
        try {
            $user = $this->getAuthUser();

            // Check if admin
            if (!$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'pesan' => 'Anda bukan admin',
                ], 403);
            }

            $pengguna = User::findOrFail($id);

            // Prevent admin dari menonaktifkan diri sendiri
            if ($pengguna->id === $user->id) {
                return response()->json([
                    'success' => false,
                    'pesan' => 'Anda tidak bisa menonaktifkan akun sendiri',
                ], 400);
            }

            // ✅ FIX #5: Wrap dalam transaction
            DB::transaction(function () use ($pengguna, $user, $request) {
                // Update status
                $pengguna->update(['is_active' => false]);

                // Log activity
                ActivityLog::create([
                    'user_id' => $user->id,
                    'action' => 'nonaktifkan_user',
                    'description' => 'Admin ' . $user->name . ' menonaktifkan akun ' . $pengguna->name,
                    'ip_address' => $request->ip(),
                ]);
            });

            return response()->json([
                'success' => true,
                'pesan' => 'Pengguna berhasil dinonaktifkan',
                'data' => [
                    'id' => $pengguna->id,
                    'name' => $pengguna->name,
                    'is_active' => $pengguna->is_active,
                ],
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'pesan' => 'Pengguna tidak ditemukan',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'pesan' => 'Error menonaktifkan pengguna',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * AKTIFKAN PENGGUNA - Aktifkan user account
     * 
     * PUT /api/v1/admin/pengguna/{id}/aktif
     * 
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function aktifkanPengguna($id, Request $request)
    {
        try {
            $user = $this->getAuthUser();

            // Check if admin
            if (!$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'pesan' => 'Anda bukan admin',
                ], 403);
            }

            $pengguna = User::findOrFail($id);

            // ✅ FIX #5: Wrap dalam transaction
            DB::transaction(function () use ($pengguna, $user, $request) {
                // Update status
                $pengguna->update(['is_active' => true]);

                // Log activity
                ActivityLog::create([
                    'user_id' => $user->id,
                    'action' => 'aktifkan_user',
                    'description' => 'Admin ' . $user->name . ' mengaktifkan kembali akun ' . $pengguna->name,
                    'ip_address' => $request->ip(),
                ]);
            });

            return response()->json([
                'success' => true,
                'pesan' => 'Pengguna berhasil diaktifkan',
                'data' => [
                    'id' => $pengguna->id,
                    'name' => $pengguna->name,
                    'is_active' => $pengguna->is_active,
                ],
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'pesan' => 'Pengguna tidak ditemukan',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'pesan' => 'Error mengaktifkan pengguna',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * HAPUS PENGGUNA - Delete user dan related data (Soft Delete Recommended)
     * 
     * DELETE /api/v1/admin/pengguna/{id}
     * 
     * ✅ FIX #8: Better cascade delete dengan chat messages
     * 
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function hapusPengguna($id, Request $request)
    {
        try {
            $user = $this->getAuthUser();

            // Check if admin
            if (!$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'pesan' => 'Anda bukan admin',
                ], 403);
            }

            $pengguna = User::findOrFail($id);

            // Prevent admin dari menghapus diri sendiri
            if ($pengguna->id === $user->id) {
                return response()->json([
                    'success' => false,
                    'pesan' => 'Anda tidak bisa menghapus akun sendiri',
                ], 400);
            }

            $nama = $pengguna->name;

            // ✅ FIX #5: Wrap dalam transaction untuk atomic operation
            DB::transaction(function () use ($pengguna) {
                // Delete related data based on role
                if ($pengguna->isPasien()) {
                    $pasien = $pengguna->pasien;
                    
                    // Delete chat messages related to patient's consultations
                    $pesanChatIds = $pasien->konsultasi()->pluck('id');
                    PesanChat::whereIn('konsultasi_id', $pesanChatIds)->delete();
                    
                    // Delete consultations
                    $pasien->konsultasi()->delete();
                    
                    // Delete medical records
                    $pasien->rekamMedis()->delete();
                    
                    // Delete patient record
                    $pasien->delete();
                } elseif ($pengguna->isDokter()) {
                    $dokter = $pengguna->dokter;
                    
                    // Delete chat messages related to doctor's consultations
                    $pesanChatIds = $dokter->konsultasi()->pluck('id');
                    PesanChat::whereIn('konsultasi_id', $pesanChatIds)->delete();
                    
                    // Delete consultations
                    $dokter->konsultasi()->delete();
                    
                    // Delete doctor record
                    $dokter->delete();
                } elseif ($pengguna->isAdmin()) {
                    // Delete admin record
                    $pengguna->admin()->delete();
                }

                // Delete user
                $pengguna->delete();
            });

            // Log activity
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'delete_user',
                'description' => 'Admin ' . $user->name . ' menghapus akun ' . $nama,
                'ip_address' => $request->ip(),
            ]);

            return response()->json([
                'success' => true,
                'pesan' => 'Pengguna dan data terkait berhasil dihapus',
                'data' => [
                    'user_id' => $id,
                    'name' => $nama,
                    'deleted_at' => now(),
                ],
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'pesan' => 'Pengguna tidak ditemukan',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'pesan' => 'Error menghapus pengguna',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * LOG AKTIVITAS - Tampilkan activity logs
     * 
     * GET /api/v1/admin/log-aktivitas
     * 
     * Query Parameters:
     * - user_id: Filter by user
     * - aksi: Filter by action
     * - tipe_model: Filter by model type
     * - days: Last X days (default: 7)
     * - per_page: Jumlah data per halaman (default: 20)
     * - sort: Field untuk sorting (default: created_at)
     * - order: asc atau desc (default: desc)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logAktivitas(Request $request)
    {
        try {
            $user = $this->getAuthUser();

            // Check if admin
            if (!$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'pesan' => 'Anda bukan admin',
                ], 403);
            }

            $userId = $request->get('user_id');
            $aksi = $request->get('aksi');
            $tipeModel = $request->get('tipe_model');
            $days = $request->get('days', 7);
            $perPage = $request->get('per_page', 20);
            $sort = $request->get('sort', 'created_at');
            $order = $request->get('order', 'desc');

            // Validate pagination parameters
            $perPage = intval($perPage) > 0 ? intval($perPage) : 20;
            $days = intval($days) > 0 ? intval($days) : 7;
            
            // Whitelist sort fields to prevent injection
            $allowedSorts = ['created_at', 'id', 'user_id', 'action'];
            $sort = in_array($sort, $allowedSorts) ? $sort : 'created_at';
            $order = strtolower($order) === 'asc' ? 'asc' : 'desc';

            $query = ActivityLog::query();

            // Add user relationship with safety
            try {
                $query->with('pengguna');
            } catch (\Exception $e) {
                \Log::warning('Could not load pengguna relationship: ' . $e->getMessage());
            }

            // Filters
            if ($userId && is_numeric($userId)) {
                $query->where('user_id', intval($userId));
            }

            if ($aksi && !empty(trim($aksi))) {
                $query->where('action', trim($aksi));
            }

            // Date filter
            $query->where('created_at', '>=', now()->subDays($days));

            // Sort
            $query->orderBy($sort, $order);

            // Paginate
            $logs = $query->paginate($perPage);

            // Map data to ensure proper structure
            $logsData = $logs->items();
            $mappedLogs = [];
            foreach ($logsData as $log) {
                $mappedLogs[] = [
                    'id' => $log->id,
                    'user_id' => $log->user_id,
                    'user' => $log->pengguna?->name ?? 'Unknown User',
                    'action' => $log->action ?? '',
                    'description' => $log->description ?? '',
                    'ip_address' => $log->ip_address ?? '',
                    'created_at' => $log->created_at?->toIso8601String() ?? null,
                ];
            }

            return response()->json([
                'success' => true,
                'pesan' => 'Log aktivitas berhasil diambil',
                'data' => $mappedLogs,
                'pagination' => [
                    'total' => $logs->total(),
                    'per_page' => $logs->perPage(),
                    'current_page' => $logs->currentPage(),
                    'last_page' => $logs->lastPage(),
                ],
            ], 200);
        } catch (\Exception $e) {
            \Log::error('LogAktivitas error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'pesan' => 'Error mengambil log aktivitas',
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
    }

    /**
     * STATISTIK - Tampilkan statistik sistem detail
     * 
     * GET /api/v1/admin/statistik
     * 
     * Menampilkan:
     * - User statistics (total, active, inactive)
     * - Consultation statistics (by status, trends)
     * - Doctor statistics (by specialty, availability)
     * - Patient statistics
    /**
     * Get system statistics
     * 
     * Endpoint untuk dashboard yang mengambil:
     * - User statistics
     * - Consultation statistics
     * - Doctor statistics
     * - Patient statistics
     * - 12-month trends
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function statistik()
    {
        try {
            $user = $this->getAuthUser();

            // Check if admin
            if (!$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'pesan' => 'Anda bukan admin',
                ], 403);
            }

            // ✅ OPTIMIZED: Use cache service untuk reduce database load
            $stats = DashboardCacheService::getAllStats();

            return response()->json([
                'success' => true,
                'pesan' => 'Statistik sistem berhasil diambil',
                'data' => $stats,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'pesan' => 'Error mengambil statistik',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * DOCTOR APPROVAL - Get pending doctors untuk diverifikasi
     * 
     * GET /api/v1/admin/dokter/pending
     */
    public function getPendingDoctors()
    {
        try {
            $user = $this->getAuthUser();

            // Check if admin
            if (!$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'pesan' => 'Anda bukan admin',
                ], 403);
            }

            $pendingDoctors = Dokter::with('user')
                ->where('is_verified', false)
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(function ($doctor) {
                    return [
                        'id' => $doctor->id,
                        'name' => $doctor->user->name,
                        'email' => $doctor->user->email,
                        'specialization' => $doctor->specialization,
                        'license_number' => $doctor->license_number,
                        'phone_number' => $doctor->phone_number,
                        'created_at' => $doctor->created_at,
                    ];
                });

            return response()->json([
                'success' => true,
                'pesan' => 'Data dokter pending berhasil diambil',
                'data' => $pendingDoctors,
                'count' => $pendingDoctors->count(),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'pesan' => 'Error mengambil data dokter pending',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * DOCTOR APPROVAL - Get approved doctors
     * 
     * GET /api/v1/admin/dokter/approved
     */
    public function getApprovedDoctors()
    {
        try {
            $user = $this->getAuthUser();

            // Check if admin
            if (!$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'pesan' => 'Anda bukan admin',
                ], 403);
            }

            $approvedDoctors = Dokter::with('user')
                ->where('is_verified', true)
                ->orderBy('verified_at', 'desc')
                ->get()
                ->map(function ($doctor) {
                    return [
                        'id' => $doctor->id,
                        'name' => $doctor->user->name,
                        'email' => $doctor->user->email,
                        'specialization' => $doctor->specialization,
                        'license_number' => $doctor->license_number,
                        'phone_number' => $doctor->phone_number,
                        'verified_at' => $doctor->verified_at,
                        'is_available' => $doctor->is_available,
                    ];
                });

            return response()->json([
                'success' => true,
                'pesan' => 'Data dokter approved berhasil diambil',
                'data' => $approvedDoctors,
                'count' => $approvedDoctors->count(),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'pesan' => 'Error mengambil data dokter approved',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * DOCTOR APPROVAL - Approve a doctor
     * 
     * POST /api/v1/admin/dokter/{id}/approve
     * 
     * Request body:
     * {
     *   "notes": "Lisensi sudah diverifikasi" (optional)
     * }
     */
    public function approveDoctor($id, Request $request)
    {
        try {
            $user = $this->getAuthUser();

            // Check if admin
            if (!$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'pesan' => 'Anda bukan admin',
                ], 403);
            }

            $request->validate([
                'notes' => 'nullable|string|max:500',
            ]);

            $doctor = Dokter::with('user')->findOrFail($id);

            if ($doctor->is_verified) {
                return response()->json([
                    'success' => false,
                    'pesan' => 'Dokter sudah diverifikasi sebelumnya',
                ], 400);
            }

            // Update doctor status
            $doctor->update([
                'is_verified' => true,
                'verified_at' => now(),
                'verified_by_admin_id' => $user->id,
                'verification_notes' => $request->notes ?? '',
            ]);

            // Send approval email
            try {
                \Illuminate\Support\Facades\Mail::to($doctor->user->email)->send(new \App\Mail\DoctorApprovedMail($doctor));
            } catch (\Exception $e) {
                \Log::error('Failed to send doctor approval email: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'pesan' => 'Dokter berhasil diverifikasi',
                'data' => [
                    'id' => $doctor->id,
                    'name' => $doctor->user->name,
                    'specialization' => $doctor->specialization,
                    'verified_at' => $doctor->verified_at,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'pesan' => 'Error saat verifikasi dokter',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * DOCTOR APPROVAL - Reject a doctor
     * 
     * POST /api/v1/admin/dokter/{id}/reject
     * 
     * Request body:
     * {
     *   "reason": "Lisensi tidak valid"
     * }
     */
    public function rejectDoctor($id, Request $request)
    {
        try {
            $user = $this->getAuthUser();

            // Check if admin
            if (!$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'pesan' => 'Anda bukan admin',
                ], 403);
            }

            $request->validate([
                'reason' => 'required|string|max:500',
            ]);

            $doctor = Dokter::with('user')->findOrFail($id);

            if ($doctor->is_verified) {
                return response()->json([
                    'success' => false,
                    'pesan' => 'Tidak dapat menolak dokter yang sudah diverifikasi',
                ], 400);
            }

            // Store rejection reason
            $doctor->update([
                'verification_notes' => 'REJECTED: ' . $request->reason,
            ]);

            // Send rejection email
            try {
                \Illuminate\Support\Facades\Mail::to($doctor->user->email)->send(new \App\Mail\DoctorRejectedMail($doctor, $request->reason));
            } catch (\Exception $e) {
                \Log::error('Failed to send doctor rejection email: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'pesan' => 'Pendaftaran dokter ditolak',
                'data' => [
                    'id' => $doctor->id,
                    'name' => $doctor->user->name,
                    'reason' => $request->reason,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'pesan' => 'Error saat menolak dokter',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get system audit logs (Superadmin only)
     * 
     * GET /api/v1/superadmin/system-logs
     */
    public function getSystemLogs(Request $request)
    {
        try {
            $user = $this->getAuthUser();

            // Check if superadmin
            if (!$user->isSuperAdmin()) {
                return response()->json([
                    'success' => false,
                    'pesan' => 'Hanya superadmin yang bisa mengakses system logs',
                ], 403);
            }

            $query = SystemLog::with('admin')->recent();

            // Apply filters
            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('action', 'like', "%$search%")
                      ->orWhere('resource', 'like', "%$search%")
                      ->orWhere('ip_address', 'like', "%$search%");
                });
            }

            if ($request->filled('action')) {
                $query->byAction($request->input('action'));
            }

            if ($request->filled('resource')) {
                $query->byResource($request->input('resource'));
            }

            if ($request->filled('status')) {
                $query->byStatus($request->input('status'));
            }

            $logs = $query->paginate($request->input('per_page', 25));

            return response()->json([
                'success' => true,
                'pesan' => 'System logs berhasil diambil',
                'data' => $logs->items(),
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'total' => $logs->total(),
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error getting system logs: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'pesan' => 'Error mengambil system logs',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update user status (activate/deactivate)
     * 
     * PUT /api/v1/admin/pengguna/{id}/status
     */
    public function updateUserStatus(Request $request, $id)
    {
        try {
            $user = $this->getAuthUser();

            // Check if admin or superadmin
            if (!$user->isAdmin() && !$user->isSuperAdmin()) {
                return response()->json([
                    'success' => false,
                    'pesan' => 'Anda tidak memiliki hak akses',
                ], 403);
            }

            $validate = $request->validate([
                'is_active' => 'required|boolean',
            ]);

            $targetUser = User::find($id);
            if (!$targetUser) {
                return response()->json([
                    'success' => false,
                    'pesan' => 'User tidak ditemukan',
                ], 404);
            }

            $targetUser->update(['is_active' => $validate['is_active']]);

            // Log the action
            SystemLog::logAction(
                $user->id,
                'update',
                'user',
                $id,
                $request->ip(),
                ['is_active' => $validate['is_active']],
                $request->userAgent(),
                'success'
            );

            return response()->json([
                'success' => true,
                'pesan' => 'Status user berhasil diubah',
                'data' => [
                    'id' => $targetUser->id,
                    'is_active' => $targetUser->is_active,
                ],
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'pesan' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error updating user status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'pesan' => 'Error mengubah status user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}