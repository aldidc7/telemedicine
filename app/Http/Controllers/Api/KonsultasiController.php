<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use App\Http\Requests\ConsultationRequest;
use App\Services\ConsultationService;
use App\Models\Konsultasi;
use App\Models\Pasien;
use App\Models\Dokter;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * ============================================
 * KONTROLER KONSULTASI - CRUD OPERATIONS
 * ============================================
 * 
 * File ini berfungsi untuk:
 * - GET /api/v1/konsultasi - List konsultasi user
 * - POST /api/v1/konsultasi - Buat konsultasi baru
 * - GET /api/v1/konsultasi/{id} - Detail konsultasi
 * - POST /api/v1/konsultasi/{id}/terima - Dokter terima konsultasi
 * - POST /api/v1/konsultasi/{id}/tolak - Dokter tolak konsultasi
 * - POST /api/v1/konsultasi/{id}/selesaikan - Selesaikan konsultasi
 * 
 * @author Aplikasi Telemedicine RSUD dr. R. Soedarsono
 * @version 2.0
 */
class KonsultasiController extends Controller
{
    use ApiResponse;

    public function __construct(
        private ConsultationService $consultationService
    ) {}

    /**
     * LIST - Tampilkan konsultasi user (pasien/dokter/admin)
     * 
     * GET /api/v1/konsultasi
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $filters = [
            'status' => $request->get('status'),
            'pasien_id' => $user->isPasien() ? $user->pasien->id : null,
            'dokter_id' => $user->isDokter() ? $user->dokter->id : null,
        ];
        
        $consultations = $this->consultationService->getAllConsultations(
            $filters,
            $request->get('per_page', 15)
        );
        
        return $this->paginatedResponse(
            $consultations,
            'Daftar konsultasi berhasil diambil'
        );
    }

    /**
     * STORE - Buat konsultasi baru (Pasien only)
     * 
     * POST /api/v1/konsultasi
     */
    public function store(ConsultationRequest $request)
    {
        $user = Auth::user();
        
        if (!$user->isPasien()) {
            return $this->forbiddenResponse('Hanya pasien yang bisa membuat konsultasi');
        }
        
        $konsultasi = $this->consultationService->createConsultation(
            $user,
            $request->validated()
        );
        
        return $this->createdResponse(
            $konsultasi,
            'Konsultasi berhasil dibuat'
        );
    }

    /**
     * SHOW - Tampilkan detail konsultasi
     * 
     * GET /api/v1/konsultasi/{id}
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * SHOW - Detail konsultasi
     * 
     * GET /api/v1/konsultasi/{id}
     */
    public function show($id)
    {
        $konsultasi = $this->consultationService->getConsultationById($id);
        
        if (!$konsultasi) {
            return $this->notFoundResponse('Konsultasi tidak ditemukan');
        }
        
        // Authorization check
        $user = Auth::user();
        $isAuthorized = ($user->isPasien() && $user->pasien->id === $konsultasi->pasien_id) ||
                        ($user->isDokter() && $user->dokter->id === $konsultasi->dokter_id) ||
                        $user->isAdmin();
        
        if (!$isAuthorized) {
            return $this->forbiddenResponse('Anda tidak berhak mengakses konsultasi ini');
        }
        
        return $this->successResponse($konsultasi, 'Detail konsultasi berhasil diambil');
    }

    /**
     * ACCEPT - Dokter menerima konsultasi
     * 
     * POST /api/v1/konsultasi/{id}/terima
     */
    public function terima($id)
    {
        $konsultasi = Konsultasi::find($id);
        
        if (!$konsultasi) {
            return $this->notFoundResponse('Konsultasi tidak ditemukan');
        }
        
        $user = Auth::user();
        
        if (!$user->isDokter() || $konsultasi->dokter_id !== $user->dokter->id) {
            return $this->forbiddenResponse('Konsultasi bukan untuk Anda');
        }
        
        if ($konsultasi->status !== 'pending') {
            return $this->errorResponse('Konsultasi tidak dalam status menunggu', 400);
        }
        
        $updated = $this->consultationService->updateConsultation($konsultasi, [
            'status' => 'active',
        ]);
        
        return $this->successResponse($updated, 'Konsultasi berhasil diterima');
    }

    /**
     * REJECT - Dokter menolak konsultasi
     * 
     * POST /api/v1/konsultasi/{id}/tolak
     */
    public function tolak($id, Request $request)
    {
        $konsultasi = Konsultasi::find($id);
        
        if (!$konsultasi) {
            return $this->notFoundResponse('Konsultasi tidak ditemukan');
        }
        
        $user = Auth::user();
        
        if (!$user->isDokter() || $konsultasi->dokter_id !== $user->dokter->id) {
            return $this->forbiddenResponse('Konsultasi bukan untuk Anda');
        }
        
        if ($konsultasi->status !== 'pending') {
            return $this->errorResponse('Konsultasi tidak dalam status menunggu', 400);
        }
        
        $updated = $this->consultationService->completeConsultation($konsultasi, [
            'status' => 'cancelled',
            'closing_notes' => $request->input('alasan', 'Ditolak oleh dokter'),
        ]);
        
        return $this->successResponse($updated, 'Konsultasi berhasil ditolak');
    }

    /**
     * COMPLETE - Selesaikan konsultasi
     * 
     * POST /api/v1/konsultasi/{id}/selesaikan
     */
    public function selesaikan($id, Request $request)
    {
        $konsultasi = Konsultasi::find($id);
        
        if (!$konsultasi) {
            return $this->notFoundResponse('Konsultasi tidak ditemukan');
        }
        
        $user = Auth::user();
        
        if (!$user->isDokter() || $konsultasi->dokter_id !== $user->dokter->id) {
            return $this->forbiddenResponse('Konsultasi bukan untuk Anda');
        }
        
        if ($konsultasi->status !== 'active') {
            return $this->errorResponse('Konsultasi tidak dalam status aktif', 400);
        }
        
        $updated = $this->consultationService->completeConsultation($konsultasi, [
            'status' => 'closed',
            'end_time' => now(),
            'closing_notes' => $request->input('catatan_penutup'),
        ]);
        
        return $this->successResponse($updated, 'Konsultasi berhasil diselesaikan');
    }
}