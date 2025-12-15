<?php

namespace App\Http\Controllers\SimrsApi;

use App\Http\Controllers\Controller;
use App\Models\Dokter;
use Illuminate\Http\Request;

/**
 * ============================================
 * KONTROLER DOKTER - SIMULASI SIMRS
 * ============================================
 * 
 * File ini berfungsi untuk:
 * - Mengembalikan data dokter dari SIMRS dummy
 * - Mengambil profil dokter, spesialisasi, lisensi
 * - Menyediakan daftar dokter yang tersedia
 * - Validasi lisensi dokter
 * 
 * Endpoint:
 * GET /api/v1/simrs/dokter/{id}
 * GET /api/v1/simrs/dokter/spesialisasi/{spesialisasi}
 * 
 * @author Aplikasi Telemedicine RSUD dr. R. Soedarsono
 * @version 1.0
 */
class SimrsDokterController extends Controller
{
    /**
     * Ambil data dokter dari SIMRS berdasarkan ID
     * 
     * Fungsi ini mengambil data profil dokter dari SIMRS dummy.
     * Data yang dikembalikan termasuk spesialisasi, lisensi, jam praktek, dll.
     * 
     * GET /api/v1/simrs/dokter/{id_dokter}
     * 
     * Header Required:
     * - Authorization: Bearer {token_simrs}
     * 
     * @param int $id_dokter - ID dokter yang dicari
     * @param Request $request - HTTP request object
     * 
     * @return \Illuminate\Http\JsonResponse
     *   Response success (200):
     *   {
     *     "success": true,
     *     "data": {
     *       "id_dokter": "DOC-001",
     *       "nama_lengkap": "Dr. Setiawan Wijaya",
     *       "spesialisasi": "Dokter Anak",
     *       ...
     *     }
     *   }
     * 
     *   Response not found (404):
     *   {
     *     "success": false,
     *     "pesan": "Data dokter tidak ditemukan"
     *   }
     */
    public function ambil($id_dokter, Request $request)
    {
        // ============ STEP 1: VALIDASI TOKEN ============
        $validasi_result = $this->validasiTokenSimrs($request);
        if ($validasi_result !== true) {
            return $validasi_result;
        }

        // ============ STEP 2: DUMMY DATA DOKTER ============
        // Dalam implementasi real, data diambil dari database SIMRS
        // Untuk dummy, data hardcoded untuk testing
        $data_dokter = [
            1 => [
                'id_dokter' => 'DOC-001',
                'nama_lengkap' => 'Dr. Setiawan Wijaya',
                'spesialisasi' => 'Dokter Anak',
                'no_sip' => 'SIP-JE-2020-000001',
                'no_str' => 'STR-2018-00001',
                'no_telepon' => '081999888777',
                'email' => 'dr.setiawan@rsud.go.id',
                'jam_praktek' => [
                    'senin' => '08:00 - 12:00',
                    'rabu' => '14:00 - 17:00',
                ],
                'status_aktif' => true,
            ],
            2 => [
                'id_dokter' => 'DOC-002',
                'nama_lengkap' => 'Dr. Sinta Nurmalasari',
                'spesialisasi' => 'Dokter Anak',
                'no_sip' => 'SIP-JE-2019-000002',
                'no_str' => 'STR-2017-00002',
                'no_telepon' => '081888777666',
                'email' => 'dr.sinta@rsud.go.id',
                'jam_praktek' => [
                    'selasa' => '08:00 - 12:00',
                    'kamis' => '14:00 - 17:00',
                ],
                'status_aktif' => true,
            ],
        ];

        // ============ STEP 3: CEK DATA DOKTER ============
        // Cek apakah dokter dengan ID tersebut ada
        if (!isset($data_dokter[$id_dokter])) {
            return response()->json([
                'success' => false,
                'pesan' => 'Data dokter tidak ditemukan',
            ], 404);
        }

        // ============ STEP 4: RETURN DATA DOKTER ============
        return response()->json([
            'success' => true,
            'pesan' => 'Data dokter berhasil diambil',
            'data' => $data_dokter[$id_dokter],
        ], 200);
    }

    /**
     * Ambil daftar dokter berdasarkan spesialisasi
     * 
     * Fungsi ini mengambil daftar semua dokter dengan spesialisasi tertentu.
     * Berguna untuk menampilkan list dokter yang tersedia untuk konsultasi.
     * 
     * GET /api/v1/simrs/dokter/spesialisasi/{spesialisasi}
     * 
     * Header Required:
     * - Authorization: Bearer {token_simrs}
     * 
     * @param string $spesialisasi - Spesialisasi dokter (anak, umum, dll)
     * @param Request $request - HTTP request object
     * 
     * @return \Illuminate\Http\JsonResponse
     *   Response success (200):
     *   {
     *     "success": true,
     *     "data": [
     *       {
     *         "id_dokter": "DOC-001",
     *         "nama_lengkap": "Dr. Setiawan Wijaya",
     *         "spesialisasi": "Dokter Anak",
     *         "status_tersedia": true
     *       },
     *       ...
     *     ],
     *     "jumlah": 2
     *   }
     */
    public function daftarBySpesialisasi($spesialisasi, Request $request)
    {
        // ============ STEP 1: VALIDASI TOKEN ============
        $validasi_result = $this->validasiTokenSimrs($request);
        if ($validasi_result !== true) {
            return $validasi_result;
        }

        // ============ STEP 2: DUMMY DATA DOKTER PER SPESIALISASI ============
        // Dalam implementasi real, query ke table dokter dengan filter spesialisasi
        $data_dokter_spesialisasi = [
            'anak' => [
                [
                    'id_dokter' => 'DOC-001',
                    'nama_lengkap' => 'Dr. Setiawan Wijaya',
                    'spesialisasi' => 'Dokter Anak',
                    'status_tersedia' => true,
                ],
                [
                    'id_dokter' => 'DOC-002',
                    'nama_lengkap' => 'Dr. Sinta Nurmalasari',
                    'spesialisasi' => 'Dokter Anak',
                    'status_tersedia' => true,
                ],
            ],
            'umum' => [
                [
                    'id_dokter' => 'DOC-003',
                    'nama_lengkap' => 'Dr. Bambang Irawan',
                    'spesialisasi' => 'Dokter Umum',
                    'status_tersedia' => true,
                ],
            ],
        ];

        // ============ STEP 3: NORMALIZE SPESIALISASI ============
        // Convert ke lowercase untuk matching
        $spesialisasi = strtolower($spesialisasi);

        // ============ STEP 4: CEK DATA SPESIALISASI ============
        // Jika spesialisasi tidak ada, return array kosong
        if (!isset($data_dokter_spesialisasi[$spesialisasi])) {
            return response()->json([
                'success' => true,
                'data' => [],
                'jumlah' => 0,
                'pesan' => 'Tidak ada dokter dengan spesialisasi tersebut',
            ], 200);
        }

        // ============ STEP 5: RETURN DAFTAR DOKTER ============
        $daftar_dokter = $data_dokter_spesialisasi[$spesialisasi];
        
        return response()->json([
            'success' => true,
            'pesan' => 'Daftar dokter berhasil diambil',
            'data' => $daftar_dokter,
            'jumlah' => count($daftar_dokter),
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