<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PesanChat;
use App\Models\Konsultasi;
use Illuminate\Http\Request;
use App\Http\Requests\PesanChatRequest;
use App\Services\PesanChatService;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Auth;
use App\Logging\Logger;
use App\Events\PesanChatSent;
use App\Events\PesanChatDibaca;

/**
 * ============================================
 * KONTROLER PESAN CHAT - CRUD OPERATIONS
 * ============================================
 * 
 * File ini berfungsi untuk:
 * - GET /api/v1/pesan/{konsultasi_id} - List pesan dalam konsultasi
 * - POST /api/v1/pesan - Kirim pesan baru
 * - GET /api/v1/pesan/{id} - Detail pesan
 * - PUT /api/v1/pesan/{id}/dibaca - Mark pesan as read
 * - GET /api/v1/pesan/{konsultasi_id}/unread-count - Hitung pesan belum dibaca
 * - DELETE /api/v1/pesan/{id} - Delete pesan (sender only)
 * - PUT /api/v1/pesan/{konsultasi_id}/mark-all-read - Mark all as read
 * 
 * @author Aplikasi Telemedicine RSUD dr. R. Soedarsono
 * @version 1.0
 */
class PesanChatController extends Controller
{
    use ApiResponse;

    private $pesanChatService;

    /**
     * Constructor with dependency injection
     * 
     * @param PesanChatService $pesanChatService
     */
    public function __construct(PesanChatService $pesanChatService)
    {
        $this->pesanChatService = $pesanChatService;
    }
    /**
     * LIST - Tampilkan pesan dalam konsultasi
     * 
     * GET /api/v1/pesan/{konsultasi_id}
     * 
     * Query Parameters:
     * - per_page: Jumlah pesan per halaman (default: 30)
     * - limit: Batasi jumlah pesan (default: unlimited)
     * 
     * @param int $konsultasiId
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($konsultasiId, Request $request)
    {
        $konsultasi = Konsultasi::with('pasien', 'dokter')->find($konsultasiId);
        if (!$konsultasi) {
            return $this->notFoundResponse('Konsultasi tidak ditemukan');
        }

        $user = Auth::user();

        // Authorization check - hanya pasien/dokter konsultasi yang bisa lihat pesan
        $isAuthorized = false;
        if ($user->isPasien() && $user->pasien->id === $konsultasi->pasien_id) {
            $isAuthorized = true;
        } elseif ($user->isDokter() && $user->dokter->id === $konsultasi->dokter_id) {
            $isAuthorized = true;
        } elseif ($user->isAdmin()) {
            $isAuthorized = true;
        }

        if (!$isAuthorized) {
            return $this->forbiddenResponse('Anda tidak berhak mengakses pesan konsultasi ini');
        }

        $filters = [
            'konsultasi_id' => $konsultasiId,
            'limit' => $request->get('limit'),
            'per_page' => $request->get('per_page', 30),
        ];

        $result = $this->pesanChatService->getAllMessages($konsultasiId, $filters);

        if ($request->get('limit')) {
            return $this->successResponse($result['messages'], 'Daftar pesan berhasil diambil', [
                'total' => $result['total'],
            ]);
        }

        return $this->paginatedResponse($result['messages'], $result['pagination'], 'Daftar pesan berhasil diambil');
    }

    /**
     * STORE - Kirim pesan baru
     * 
     * POST /api/v1/pesan
     * 
     * Body Parameters:
     * {
     *   "konsultasi_id": 1,
     *   "pesan": "Anak saya demam tinggi...",
     *   "tipe_pesan": "text", (text/image/file/audio)
     *   "url_file": "https://..." (optional, untuk file/image)
     * }
     * 
     * @OA\Post(
     *      path="/api/v1/pesan",
     *      operationId="storePesan",
     *      tags={"Chat"},
     *      summary="Send new message",
     *      description="Kirim pesan baru dalam konsultasi (Real-time via WebSocket)",
     *      security={{"bearerAuth":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          description="Message data",
     *          @OA\JsonContent(
     *              required={"konsultasi_id", "pesan", "tipe_pesan"},
     *              @OA\Property(property="konsultasi_id", type="integer", example=1),
     *              @OA\Property(property="pesan", type="string", example="Anak saya demam tinggi"),
     *              @OA\Property(property="tipe_pesan", type="string", enum={"text", "image", "file", "audio"}),
     *              @OA\Property(property="url_file", type="string", format="uri", nullable=true),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Message sent successfully",
     *          @OA\JsonContent(ref="#/components/schemas/ApiResponse"),
     *      ),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=401, description="Unauthorized"),
     *      @OA\Response(response=403, description="Forbidden"),
     *      @OA\Response(response=404, description="Consultation not found"),
     * )
     * 
     * @param PesanChatRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(PesanChatRequest $request)
    {
        $validated = $request->validated();
        $user = Auth::user();

        // Log API request
        Logger::logApiRequest('POST', 'pesan', $validated, $user->id);

        // Get konsultasi
        $konsultasi = Konsultasi::find($validated['konsultasi_id']);
        if (!$konsultasi) {
            /** @var \Throwable $exception */
            $exception = new \RuntimeException('Konsultasi tidak ditemukan');
            Logger::logError(
                $exception,
                'PesanChatController@store',
                ['konsultasi_id' => $validated['konsultasi_id']]
            );
            return $this->notFoundResponse('Konsultasi tidak ditemukan');
        }

        // Check authorization - hanya pasien/dokter konsultasi yang bisa kirim pesan
        $isAuthorized = false;
        if ($user->isPasien() && $user->pasien->id === $konsultasi->pasien_id) {
            $isAuthorized = true;
        } elseif ($user->isDokter() && $user->dokter->id === $konsultasi->dokter_id) {
            $isAuthorized = true;
        }

        if (!$isAuthorized) {
            Logger::logApiRequest('POST', 'pesan', ['unauthorized' => true], $user->id);
            return $this->forbiddenResponse('Anda tidak berhak mengirim pesan di konsultasi ini');
        }

        // Check if consultation is active
        if ($konsultasi->status !== 'aktif') {
            return $this->badRequestResponse('Konsultasi tidak dalam status aktif. Tidak bisa mengirim pesan.');
        }

        // Create message using service
        $pesanChat = $this->pesanChatService->createMessage($user, $validated);

        // Log transaction
        Logger::logTransaction('create', 'PesanChat', $pesanChat->id, [
            'konsultasi_id' => $konsultasi->id,
        ], $user->id);

        // 🚀 BROADCAST EVENT - Real-time delivery via WebSocket
        broadcast(new PesanChatSent($pesanChat, $konsultasi))->toOthers();

        return $this->createdResponse($pesanChat, 'Pesan berhasil dikirim');
    }

    /**
     * SHOW - Tampilkan detail pesan
     * 
     * GET /api/v1/pesan/{id}
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $pesanChat = $this->pesanChatService->getMessageById($id);
        if (!$pesanChat) {
            return $this->notFoundResponse('Pesan tidak ditemukan');
        }

        $user = Auth::user();
        $konsultasi = $pesanChat->konsultasi;

        // Authorization check
        $isAuthorized = false;
        if ($user->isPasien() && $user->pasien->id === $konsultasi->pasien_id) {
            $isAuthorized = true;
        } elseif ($user->isDokter() && $user->dokter->id === $konsultasi->dokter_id) {
            $isAuthorized = true;
        } elseif ($user->isAdmin()) {
            $isAuthorized = true;
        }

        if (!$isAuthorized) {
            return $this->forbiddenResponse('Anda tidak berhak mengakses pesan ini');
        }

        return $this->successResponse($pesanChat, 'Detail pesan berhasil diambil');
    }

    /**
     * MARK AS READ - Mark pesan sebagai sudah dibaca
     * 
     * PUT /api/v1/pesan/{id}/dibaca
     * 
     * @OA\Put(
     *      path="/api/v1/pesan/{id}/dibaca",
     *      operationId="markAsDibaca",
     *      tags={"Chat"},
     *      summary="Mark message as read",
     *      description="Tandai pesan sebagai sudah dibaca (Real-time via WebSocket)",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="Message ID",
     *          required=true,
     *          @OA\Schema(type="integer"),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Message marked as read",
     *          @OA\JsonContent(ref="#/components/schemas/ApiResponse"),
     *      ),
     *      @OA\Response(response=401, description="Unauthorized"),
     *      @OA\Response(response=403, description="Forbidden"),
     *      @OA\Response(response=404, description="Message not found"),
     * )
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsDibaca($id)
    {
        $pesan = PesanChat::find($id);
        if (!$pesan) {
            return $this->notFoundResponse('Pesan tidak ditemukan');
        }

        $user = Auth::user();
        $konsultasi = $pesan->load('konsultasi.pasien', 'konsultasi.dokter')->konsultasi;

        // Authorization check - hanya penerima yang bisa mark as read
        $isPenerima = false;

        // Jika pesan dari pasien, dokter yang bisa mark as read
        if ($pesan->pengirim_id === $konsultasi->pasien->user_id &&
            $user->isDokter() && $user->dokter->id === $konsultasi->dokter_id) {
            $isPenerima = true;
        }
        // Jika pesan dari dokter, pasien yang bisa mark as read
        elseif ($pesan->pengirim_id === $konsultasi->dokter->user_id &&
                $user->isPasien() && $user->pasien->id === $konsultasi->pasien_id) {
            $isPenerima = true;
        }
        // Admin juga bisa
        elseif ($user->isAdmin()) {
            $isPenerima = true;
        }

        if (!$isPenerima) {
            return $this->forbiddenResponse('Anda tidak berhak mengubah status pesan ini');
        }

        // Mark as read using service
        $updated = $this->pesanChatService->markAsRead($pesan);

        // 🚀 BROADCAST EVENT - Real-time read receipt
        broadcast(new PesanChatDibaca($updated, $konsultasi->id))->toOthers();

        return $this->successResponse($updated, 'Pesan berhasil ditandai sebagai dibaca');
    }

    /**
     * UNREAD COUNT - Hitung pesan belum dibaca dalam konsultasi
     * 
     * GET /api/v1/pesan/{konsultasi_id}/unread-count
     * 
     * @param int $konsultasiId
     * @return \Illuminate\Http\JsonResponse
     */
    public function unreadCount($konsultasiId)
    {
        $konsultasi = Konsultasi::with('pasien', 'dokter')->find($konsultasiId);
        if (!$konsultasi) {
            return $this->notFoundResponse('Konsultasi tidak ditemukan');
        }

        $user = Auth::user();

        // Authorization check
        $isAuthorized = false;
        if ($user->isPasien() && $user->pasien->id === $konsultasi->pasien_id) {
            $isAuthorized = true;
        } elseif ($user->isDokter() && $user->dokter->id === $konsultasi->dokter_id) {
            $isAuthorized = true;
        } elseif ($user->isAdmin()) {
            $isAuthorized = true;
        }

        if (!$isAuthorized) {
            return $this->forbiddenResponse('Anda tidak berhak mengakses data ini');
        }

        // Get count from service
        $count = $this->pesanChatService->getUnreadCount($konsultasiId, Auth::id(), $user);

        return $this->successResponse([
            'konsultasi_id' => $konsultasiId,
            'unread_count' => $count,
        ], 'Jumlah pesan belum dibaca berhasil diambil');
    }

    /**
     * DELETE - Hapus pesan (sender only)
     * 
     * DELETE /api/v1/pesan/{id}
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $pesan = PesanChat::find($id);
        if (!$pesan) {
            return $this->notFoundResponse('Pesan tidak ditemukan');
        }

        $user = Auth::user();

        // Authorization check - hanya pengirim yang bisa delete
        if ($pesan->pengirim_id !== $user->id && !$user->isAdmin()) {
            return $this->forbiddenResponse('Anda hanya bisa menghapus pesan milik Anda sendiri');
        }

        // Delete using service
        $this->pesanChatService->deleteMessage($pesan);

        return $this->successResponse(null, 'Pesan berhasil dihapus');
    }

    /**
     * MARK ALL AS READ - Mark semua pesan belum dibaca sebagai dibaca
     * 
     * PUT /api/v1/pesan/{konsultasi_id}/mark-all-read
     * 
     * @param int $konsultasiId
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAllAsDibaca($konsultasiId)
    {
        $konsultasi = Konsultasi::with('pasien', 'dokter')->find($konsultasiId);
        if (!$konsultasi) {
            return $this->notFoundResponse('Konsultasi tidak ditemukan');
        }

        $user = Auth::user();

        // Authorization check
        $isAuthorized = false;
        if ($user->isPasien() && $user->pasien->id === $konsultasi->pasien_id) {
            $isAuthorized = true;
        } elseif ($user->isDokter() && $user->dokter->id === $konsultasi->dokter_id) {
            $isAuthorized = true;
        } elseif ($user->isAdmin()) {
            $isAuthorized = true;
        }

        if (!$isAuthorized) {
            return $this->forbiddenResponse('Anda tidak berhak mengakses data ini');
        }

        // Mark all as read using service
        $this->pesanChatService->markAllAsRead($konsultasiId, Auth::id(), $user);

        return $this->successResponse(null, 'Semua pesan sudah dibaca');
    }
}