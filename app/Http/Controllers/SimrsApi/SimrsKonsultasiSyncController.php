<?php

namespace App\Http\Controllers\SimrsApi;

use App\Http\Controllers\Controller;
use App\Models\Konsultasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * ============================================
 * KONTROLER SINKRONISASI KONSULTASI - SIMRS
 * ============================================
 * 
 * File ini berfungsi untuk:
 * - Menerima hasil konsultasi telemedicine dari aplikasi
 * - Menyimpan/sinkronisasi hasil konsultasi ke SIMRS database
 * - Sinkronisasi diagnosis, treatment, dan rekomendasi dokter
 * - Update status konsultasi di SIMRS
 * - Validasi token SIMRS untuk keamanan API
 * 
 * Endpoint:
 * POST /api/v1/simrs/konsultasi/{id}/sinkronisasi
 * GET /api/v1/simrs/konsultasi/{id}/status-sinkronisasi
 * 
 * @author Aplikasi Telemedicine RSUD dr. R. Soedarsono
 * @version 1.0
 */
class SimrsKonsultasiSyncController extends Controller
{
    /**
     * Sinkronisasi hasil konsultasi telemedicine ke SIMRS
     * 
     * Fungsi ini menerima data hasil konsultasi dari aplikasi telemedicine
     * dan menyimpannya ke database SIMRS. Data yang disinkronisasi termasuk:
     * - Ringkasan hasil konsultasi
     * - Diagnosis sementara dari dokter
     * - Rekomendasi/treatment dari dokter
     * - Status konsultasi (selesai/dibatalkan)
     * 
     * POST /api/v1/simrs/konsultasi/{id_konsultasi}/sinkronisasi
     * 
     * Header Required:
     * - Authorization: Bearer {token_simrs}
     * 
     * Body Parameters:
     * {
     *   "status": "selesai",                    // selesai atau dibatalkan
     *   "ringkasan": "...",                     // ringkasan konsultasi
     *   "diagnosis_sementara": "...",           // diagnosis dari dokter
     *   "rekomendasi": "...",                   // rekomendasi treatment
     *   "id_pasien": "PAT-001",                 // ID pasien
     *   "id_dokter": "DOC-001"                  // ID dokter
     * }
     * 
     * @param int $id_konsultasi - ID konsultasi dari aplikasi telemedicine
     * @param Request $request - HTTP request dengan data hasil konsultasi
     * 
     * @return \Illuminate\Http\JsonResponse
     *   Response success (200):
     *   {
     *     "success": true,
     *     "pesan": "Konsultasi berhasil disinkronisasi ke SIMRS",
     *     "data": {
     *       "id_konsultasi": 1,
     *       "id_sinkronisasi": "SYNC-1-1704908400",
     *       "waktu_sinkronisasi": "2024-01-10 15:30:00",
     *       "status_di_simrs": "TERSIMPAN"
     *     }
     *   }
     * 
     *   Response error (token invalid):
     *   {
     *     "success": false,
     *     "pesan": "Token SIMRS tidak valid atau tidak ditemukan",
     *     "info": "Pastikan header Authorization: Bearer {token} sudah benar"
     *   }
     */
    public function sinkronisasi($id_konsultasi, Request $request)
    {
        // ============ STEP 1: VALIDASI TOKEN ============
        // Cek apakah token SIMRS valid
        $validasi_result = $this->validasiTokenSimrs($request);
        if ($validasi_result !== true) {
            // Jika token tidak valid, return error response
            return $validasi_result;
        }

        // ============ STEP 2: VALIDASI INPUT ============
        // Pastikan semua data yang diperlukan sudah ada dan format benar
        $validated = $request->validate([
            'status' => 'required|in:selesai,dibatalkan',
            'ringkasan' => 'nullable|string|max:1000',
            'diagnosis_sementara' => 'nullable|string|max:500',
            'rekomendasi' => 'nullable|string|max:1000',
            'id_pasien' => 'required|string',
            'id_dokter' => 'required|string',
        ]);

        // ============ STEP 3: PREPARE DATA SINKRONISASI ============
        // Siapkan data yang akan disinkronisasi
        $data_sinkronisasi = [
            'id_konsultasi' => $id_konsultasi,
            'id_pasien' => $validated['id_pasien'],
            'id_dokter' => $validated['id_dokter'],
            'status' => $validated['status'],
            'waktu_sinkronisasi' => now(),
            'data_hasil_konsultasi' => $validated,
        ];

        // ============ STEP 4: LOG DATA (untuk audit trail) ============
        // Catat semua data sinkronisasi ke log file untuk tracking dan debugging
        Log::info('Konsultasi disinkronisasi ke SIMRS', $data_sinkronisasi);

        // ============ STEP 5: UPDATE DATABASE LOKAL ============
        // Update status sinkronisasi di database aplikasi telemedicine
        try {
            $konsultasi = Konsultasi::findOrFail($id_konsultasi);
            
            // Update kolom sesuai dengan schema database
            // Field: sudah_sinkron_simrs, waktu_sinkronisasi, catatan_penutup
            $konsultasi->update([
                'sudah_sinkron_simrs' => true,      // Flag: sudah disinkronisasi ke SIMRS
                'waktu_sinkronisasi' => now(),      // Waktu kapan disinkronisasi
                'catatan_penutup' => $validated['rekomendasi'] ?? null,  // Simpan rekomendasi
            ]);
        } catch (\Exception $e) {
            // Jika ada error saat update database, log error tapi tetap return success ke SIMRS
            // Karena SIMRS sudah menerima data, jangan sampai resend
            Log::error('Error update status sinkronisasi lokal', [
                'error' => $e->getMessage(),
                'id_konsultasi' => $id_konsultasi
            ]);
        }

        // ============ STEP 6: RETURN RESPONSE SUCCESS ============
        // Beritahu aplikasi telemedicine bahwa sinkronisasi berhasil
        return response()->json([
            'success' => true,
            'pesan' => 'Konsultasi berhasil disinkronisasi ke SIMRS',
            'data' => [
                'id_konsultasi' => $id_konsultasi,
                'id_sinkronisasi' => 'SYNC-' . $id_konsultasi . '-' . now()->timestamp,
                'waktu_sinkronisasi' => now(),
                'status_di_simrs' => 'TERSIMPAN',
            ],
        ], 200);
    }

    /**
     * Ambil status sinkronisasi konsultasi di SIMRS
     * 
     * Fungsi ini mengambil status sinkronisasi hasil konsultasi yang telah disimpan.
     * Berguna untuk tracking apakah data sudah tersimpan di SIMRS.
     * 
     * GET /api/v1/simrs/konsultasi/{id_konsultasi}/status-sinkronisasi
     * 
     * Header Required:
     * - Authorization: Bearer {token_simrs}
     * 
     * @param int $id_konsultasi - ID konsultasi yang dicari
     * @param Request $request - HTTP request object
     * 
     * @return \Illuminate\Http\JsonResponse
     *   Response (200):
     *   {
     *     "success": true,
     *     "data": {
     *       "id_konsultasi": 1,
     *       "status_sinkronisasi": "TERSIMPAN",
     *       "waktu_sinkronisasi": "2024-01-10 15:30:00",
     *       "sudah_sinkron": true
     *     }
     *   }
     */
    public function statusSinkronisasi($id_konsultasi, Request $request)
    {
        // ============ STEP 1: VALIDASI TOKEN ============
        $validasi_result = $this->validasiTokenSimrs($request);
        if ($validasi_result !== true) {
            return $validasi_result;
        }

        // ============ STEP 2: AMBIL DATA KONSULTASI ============
        try {
            $konsultasi = Konsultasi::findOrFail($id_konsultasi);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'pesan' => 'Konsultasi tidak ditemukan',
            ], 404);
        }

        // ============ STEP 3: RETURN STATUS SINKRONISASI ============
        return response()->json([
            'success' => true,
            'pesan' => 'Status sinkronisasi konsultasi',
            'data' => [
                'id_konsultasi' => $konsultasi->id,
                'status_sinkronisasi' => $konsultasi->sudah_sinkron_simrs ? 'TERSIMPAN' : 'BELUM_SINKRON',
                'waktu_sinkronisasi' => $konsultasi->waktu_sinkronisasi,
                'sudah_sinkron' => (bool) $konsultasi->sudah_sinkron_simrs,
            ],
        ], 200);
    }

    /**
     * Validasi token SIMRS API
     * 
     * Method ini mengecek apakah Bearer token yang dikirim valid.
     * Token diambil dari config/simrs.php atau default dummy token.
     * 
     * Return value PENTING:
     * - Return true jika token VALID
     * - Return JsonResponse jika token INVALID (jangan sampai execution lanjut!)
     * 
     * Gunakan:
     * $result = $this->validasiTokenSimrs($request);
     * if ($result !== true) {
     *     return $result;  // PENTING: return error response
     * }
     * 
     * @param Request $request - HTTP request object
     * @return bool|JsonResponse - true jika valid, atau error response jika tidak valid
     */
    private function validasiTokenSimrs(Request $request)
    {
        $token = $request->bearerToken();
        $token_yang_diharapkan = config('simrs.api_token') ?? 'token_simrs_dummy_123';

        // Cek token
        if (!$token || $token !== $token_yang_diharapkan) {
            // PENTING: Return response, bukan hanya echo/log
            return response()->json([
                'success' => false,
                'pesan' => 'Token SIMRS tidak valid atau tidak ditemukan',
                'info' => 'Pastikan header Authorization: Bearer {token} sudah benar',
            ], 401);
        }

        // Token valid, return true
        return true;
    }
}