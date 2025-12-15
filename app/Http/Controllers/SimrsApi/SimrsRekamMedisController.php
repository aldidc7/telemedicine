<?php

namespace App\Http\Controllers\SimrsApi;

use App\Http\Controllers\Controller;
use App\Models\RekamMedis;
use Illuminate\Http\Request;

/**
 * ============================================
 * KONTROLER REKAM MEDIS - SIMULASI SIMRS
 * ============================================
 * 
 * File ini berfungsi untuk:
 * - Mengembalikan data rekam medis pasien dari SIMRS dummy
 * - Mengambil riwayat pemeriksaan, diagnosis, dan treatment pasien
 * - Menampilkan vital signs (tekanan darah, detak jantung, dll)
 * - Menyimpan dummy data untuk testing tanpa database SIMRS asli
 * 
 * Endpoint:
 * GET /api/v1/simrs/pasien/{id_pasien}/rekam-medis
 * 
 * Catatan:
 * - Ini adalah API dummy untuk keperluan akademik/prototipe
 * - Data yang dikembalikan adalah dummy data, bukan data pasien asli
 * - Untuk integrasi SIMRS real, ganti logic di method ini
 * 
 * @author Aplikasi Telemedicine RSUD dr. R. Soedarsono
 * @version 1.0
 */
class SimrsRekamMedisController extends Controller
{
    /**
     * Ambil rekam medis pasien dari SIMRS
     * 
     * Fungsi ini mengambil data riwayat medis pasien berdasarkan ID pasien.
     * Data yang dikembalikan adalah dummy data dari array statis untuk testing.
     * 
     * GET /api/v1/simrs/pasien/{id_pasien}/rekam-medis
     * 
     * Header Required:
     * - Authorization: Bearer {token_simrs}
     * 
     * @param string $id_pasien - ID atau NIK pasien
     * @param Request $request - HTTP request object
     * 
     * @return \Illuminate\Http\JsonResponse
     *   Response success (200):
     *   {
     *     "success": true,
     *     "data": [
     *       {
     *         "id_rekam_medis": "MR-001-001",
     *         "tanggal_kunjungan": "2023-12-15",
     *         "keluhan_utama": "Demam tinggi",
     *         "diagnosis": "ISPA Ringan",
     *         "treatment": "Istirahat, minum banyak air",
     *         "nama_dokter": "Dr. Andi Wijaya",
     *         "vital_sign": { ... }
     *       }
     *     ],
     *     "jumlah_rekam_medis": 1
     *   }
     * 
     *   Response not found (404):
     *   {
     *     "success": false,
     *     "data": [],
     *     "pesan": "Rekam medis tidak ditemukan untuk pasien ini"
     *   }
     */
    public function ambil($id_pasien, Request $request)
    {
        // ============ STEP 1: VALIDASI TOKEN ============
        $validasi_result = $this->validasiTokenSimrs($request);
        if ($validasi_result !== true) {
            return $validasi_result;
        }

        // ============ STEP 2: DUMMY DATA REKAM MEDIS ============
        // Format: NIK/ID Pasien => array dari rekam medis
        // Dalam implementasi real, data diquery dari database SIMRS
        $data_rekam_medis = [
            '3215001234567890' => [
                [
                    'id_rekam_medis' => 'MR-001-001',
                    'tanggal_kunjungan' => '2023-12-15',
                    'keluhan_utama' => 'Demam tinggi',
                    'diagnosis' => 'ISPA Ringan',
                    'treatment' => 'Istirahat, minum banyak air, parasetamol jika perlu',
                    'nama_dokter' => 'Dr. Setiawan Wijaya',
                    'spesialisasi' => 'Dokter Anak',
                    'vital_sign' => [
                        'suhu_tubuh' => 39.2,
                        'detak_jantung' => 115,
                        'tekanan_darah' => '110/65',
                        'frekuensi_napas' => 28,
                    ],
                    'catatan_tambahan' => 'Pasien disarankan untuk istirahat dan minum banyak cairan',
                ],
                [
                    'id_rekam_medis' => 'MR-001-002',
                    'tanggal_kunjungan' => '2023-11-20',
                    'keluhan_utama' => 'Batuk 2 minggu',
                    'diagnosis' => 'Batuk kering akibat alergi',
                    'treatment' => 'Antihistamin, hindari alergen',
                    'nama_dokter' => 'Dr. Sinta Nurmalasari',
                    'spesialisasi' => 'Dokter Anak',
                    'vital_sign' => [
                        'suhu_tubuh' => 36.8,
                        'detak_jantung' => 95,
                        'tekanan_darah' => '105/60',
                        'frekuensi_napas' => 22,
                    ],
                    'catatan_tambahan' => 'Pasien memiliki riwayat alergi, hindari pemicu alergi',
                ],
            ],
            '3215002345678901' => [
                [
                    'id_rekam_medis' => 'MR-002-001',
                    'tanggal_kunjungan' => '2023-12-10',
                    'keluhan_utama' => 'Diare 3 hari',
                    'diagnosis' => 'Gastroenteritis akut',
                    'treatment' => 'Rehidrasi oral, diet BRAT',
                    'nama_dokter' => 'Dr. Bambang Irawan',
                    'spesialisasi' => 'Dokter Anak',
                    'vital_sign' => [
                        'suhu_tubuh' => 37.5,
                        'detak_jantung' => 110,
                        'tekanan_darah' => '100/60',
                        'frekuensi_napas' => 24,
                    ],
                    'catatan_tambahan' => 'Pasien perlu terpantau intake cairan dan output',
                ],
            ],
            '3215003456789012' => [
                [
                    'id_rekam_medis' => 'MR-003-001',
                    'tanggal_kunjungan' => '2023-12-05',
                    'keluhan_utama' => 'Alergi kulit',
                    'diagnosis' => 'Dermatitis alergi',
                    'treatment' => 'Krim steroid, antihistamin oral',
                    'nama_dokter' => 'Dr. Setiawan Wijaya',
                    'spesialisasi' => 'Dokter Anak',
                    'vital_sign' => [
                        'suhu_tubuh' => 36.9,
                        'detak_jantung' => 92,
                        'tekanan_darah' => '108/62',
                        'frekuensi_napas' => 20,
                    ],
                    'catatan_tambahan' => 'Gunakan krim steroid 2x sehari selama 1 minggu',
                ],
            ],
        ];

        // ============ STEP 3: CEK DATA REKAM MEDIS ============
        // Cek apakah data pasien ada
        if (!isset($data_rekam_medis[$id_pasien])) {
            return response()->json([
                'success' => false,
                'data' => [],
                'pesan' => 'Rekam medis tidak ditemukan untuk pasien ini',
                'timestamp' => now(),
            ], 404);
        }

        // ============ STEP 4: RETURN DATA REKAM MEDIS ============
        $rekam_medis = $data_rekam_medis[$id_pasien];
        
        return response()->json([
            'success' => true,
            'pesan' => 'Data rekam medis berhasil diambil',
            'data' => $rekam_medis,
            'jumlah_rekam_medis' => count($rekam_medis),
            'timestamp' => now(),
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
        // Ambil token dari header Authorization: Bearer {token}
        $token = $request->bearerToken();

        // Token yang diharapkan (bisa dari config/simrs.php atau .env)
        $token_yang_diharapkan = config('simrs.api_token') ?? 'token_simrs_dummy_123';

        // Cek token
        if (!$token || $token !== $token_yang_diharapkan) {
            // PENTING: Return response, bukan hanya echo/log
            return response()->json([
                'success' => false,
                'pesan' => 'Token SIMRS tidak valid atau tidak ditemukan',
                'info' => 'Pastikan header Authorization: Bearer {token} sudah benar',
                'timestamp' => now(),
            ], 401);
        }

        // Token valid, return true
        return true;
    }
}