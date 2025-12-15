<?php

namespace App\Http\Controllers\SimrsApi;

use App\Http\Controllers\Controller;
use App\Models\Pasien;
use App\Models\Dokter;
use App\Models\RekamMedis;
use App\Models\Konsultasi;
use Illuminate\Http\Request;

/**
 * ============================================
 * KONTROLER SIMRS GENERIC - SIMULASI SIMRS
 * ============================================
 * 
 * File ini berfungsi untuk:
 * - Endpoint generic/utility untuk SIMRS API
 * - Health check status SIMRS API
 * - Validasi koneksi SIMRS
 * 
 * Endpoint:
 * GET /api/v1/simrs/health
 * GET /api/v1/simrs/status
 * 
 * Catatan:
 * - Untuk endpoint spesifik data (pasien, dokter, rekam medis, konsultasi)
 *   gunakan controller yang sesuai:
 *   - SimrsPasienController
 *   - SimrsDokterController
 *   - RekamMedisSimrsController
 *   - SimrsKonsultasiSyncController
 * 
 * @author Aplikasi Telemedicine RSUD dr. R. Soedarsono
 * @version 1.0
 */
class SimrsController extends Controller
{
    /**
     * Health check untuk SIMRS API
     * Mengecek apakah API SIMRS berjalan dengan baik
     * 
     * GET /api/v1/simrs/health
     * 
     * @return \Illuminate\Http\JsonResponse
     *   Response:
     *   {
     *     "success": true,
     *     "pesan": "SIMRS API siap digunakan",
     *     "status": "healthy",
     *     "waktu": "2024-01-10 15:30:00"
     *   }
     */
    public function health()
    {
        return response()->json([
            'success' => true,
            'pesan' => 'SIMRS API siap digunakan',
            'status' => 'healthy',
            'waktu' => now(),
        ], 200);
    }

    /**
     * Status SIMRS API - info lebih lengkap
     * Mengembalikan informasi detail tentang SIMRS API
     * 
     * GET /api/v1/simrs/status
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function status()
    {
        // Hitung total data yang ada di database lokal
        $jumlah_pasien = Pasien::count();
        $jumlah_dokter = Dokter::count();
        $jumlah_konsultasi = Konsultasi::count();
        $jumlah_rekam_medis = RekamMedis::count();

        return response()->json([
            'success' => true,
            'pesan' => 'Status SIMRS API',
            'status' => 'operational',
            'data' => [
                'api_version' => '1.0',
                'database' => [
                    'pasien' => $jumlah_pasien,
                    'dokter' => $jumlah_dokter,
                    'konsultasi' => $jumlah_konsultasi,
                    'rekam_medis' => $jumlah_rekam_medis,
                ],
                'waktu_server' => now(),
                'timezone' => config('app.timezone'),
            ],
        ], 200);
    }

    /**
     * Validasi token SIMRS API
     * 
     * Method ini mengecek apakah Bearer token yang dikirim valid.
     * Token diambil dari config/simrs.php atau default dummy token.
     * 
     * Gunakan:
     * $result = $this->validasiTokenSimrs($request);
     * if ($result !== true) {
     *     return $result;  // Return error response
     * }
     * 
     * @param Request $request - HTTP request object
     * @return bool|JsonResponse - true jika valid, atau error response jika tidak valid
     */
    private function validasiTokenSimrs(Request $request)
    {
        $token = $request->bearerToken();
        $token_yang_diharapkan = config('simrs.api_token') ?? 'token_simrs_dummy_123';

        if (!$token || $token !== $token_yang_diharapkan) {
            return response()->json([
                'success' => false,
                'pesan' => 'Token SIMRS tidak valid atau tidak ditemukan',
                'info' => 'Pastikan header Authorization: Bearer {token} sudah benar',
            ], 401);
        }

        return true;
    }
}