<?php

namespace App\Http\Controllers\SimrsApi;

use App\Http\Controllers\Controller;
use App\Models\Pasien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * ============================================
 * KONTROLER PASIEN - SIMULASI SIMRS
 * ============================================
 * 
 * File ini berfungsi untuk:
 * - Mengembalikan data demografi pasien dari SIMRS dummy
 * - Mengambil informasi dasar pasien (nama, NIK, tanggal lahir, dll)
 * - Menyediakan data pasien untuk aplikasi telemedicine
 * - Sinkronisasi data pasien baru ke SIMRS
 * 
 * Endpoint:
 * GET /api/v1/simrs/pasien/{id}
 * POST /api/v1/simrs/pasien
 * 
 * @author Aplikasi Telemedicine RSUD dr. R. Soedarsono
 * @version 1.0
 */
class SimrsPasienController extends Controller
{
    /**
     * Ambil data pasien dari SIMRS berdasarkan ID/NIK
     * 
     * Fungsi ini mengambil data demografi lengkap pasien dari SIMRS.
     * Data yang dikembalikan termasuk informasi kontak, golongan darah, alergi, dll.
     * 
     * GET /api/v1/simrs/pasien/{id_pasien}
     * 
     * Header Required:
     * - Authorization: Bearer {token_simrs}
     * 
     * @param string $id_pasien - ID atau NIK pasien
     * @param Request $request - HTTP request object
     * 
     * @return \Illuminate\Http\JsonResponse data pasien dari SIMRS
     *   Response success (200):
     *   {
     *     "success": true,
     *     "data": {
     *       "id_pasien": "PAT-001",
     *       "nik": "3215001234567890",
     *       "nama_lengkap": "Ahmad Zaki",
     *       "tanggal_lahir": "2015-06-15",
     *       ...
     *     }
     *   }
     * 
     *   Response not found (404):
     *   {
     *     "success": false,
     *     "pesan": "Data pasien tidak ditemukan"
     *   }
     */
    public function ambil($id_pasien, Request $request)
    {
        // ============ STEP 1: VALIDASI TOKEN ============
        $validasi_result = $this->validasiTokenSimrs($request);
        if ($validasi_result !== true) {
            return $validasi_result;
        }

        // ============ STEP 2: DUMMY DATA PASIEN ============
        // Format: ID pasien => data pasien
        // Dalam implementasi real, data diquery dari database SIMRS
        $data_pasien = [
            1 => [
                'id_pasien' => 'PAT-001',
                'nik' => '3215001234567890',
                'nama_lengkap' => 'Ahmad Zaki',
                'tanggal_lahir' => '2015-06-15',
                'jenis_kelamin' => 'Laki-laki',
                'umur' => 9,
                'alamat' => 'Jl. Raya Pasuruan No. 123',
                'no_telepon' => '081234567890',
                'golongan_darah' => 'O',
                'alergi' => ['Penisilin', 'Aspirin'],
                'kontak_darurat' => [
                    'nama' => 'Budi Zaki',
                    'hubungan' => 'Ayah',
                    'telepon' => '082345678901',
                ],
                'status_aktif' => true,
                'terakhir_update' => '2024-01-10 10:30:00',
            ],
            2 => [
                'id_pasien' => 'PAT-002',
                'nik' => '3215002345678901',
                'nama_lengkap' => 'Siti Nurhaliza',
                'tanggal_lahir' => '2016-08-22',
                'jenis_kelamin' => 'Perempuan',
                'umur' => 8,
                'alamat' => 'Jl. Diponegoro No. 45',
                'no_telepon' => '082123456789',
                'golongan_darah' => 'AB',
                'alergi' => ['Telur'],
                'kontak_darurat' => [
                    'nama' => 'Nur Haliza',
                    'hubungan' => 'Ibu',
                    'telepon' => '081987654321',
                ],
                'status_aktif' => true,
                'terakhir_update' => '2024-01-10 11:00:00',
            ],
        ];

        // ============ STEP 3: CEK DATA PASIEN ============
        // Cek apakah pasien dengan ID tersebut ada
        if (!isset($data_pasien[$id_pasien])) {
            return response()->json([
                'success' => false,
                'pesan' => 'Data pasien tidak ditemukan',
            ], 404);
        }

        // ============ STEP 4: RETURN DATA PASIEN ============
        return response()->json([
            'success' => true,
            'pesan' => 'Data pasien berhasil diambil',
            'data' => $data_pasien[$id_pasien],
        ], 200);
    }

    /**
     * Sinkronisasi pasien baru ke SIMRS
     * 
     * Fungsi ini menerima data pasien baru dari aplikasi telemedicine
     * dan menyimpannya ke database SIMRS.
     * 
     * POST /api/v1/simrs/pasien
     * 
     * Header Required:
     * - Authorization: Bearer {token_simrs}
     * 
     * Body Parameters:
     * {
     *   "nik": "3215003456789012",
     *   "nama_lengkap": "Budi Santoso",
     *   "tanggal_lahir": "2017-03-10",
     *   "jenis_kelamin": "Laki-laki",
     *   "alamat": "Jl. Gatot Subroto No. 10",
     *   "no_telepon": "083456789012"
     * }
     * 
     * @param Request $request - HTTP request dengan data pasien baru
     * 
     * @return \Illuminate\Http\JsonResponse
     *   Response success (201):
     *   {
     *     "success": true,
     *     "pesan": "Data pasien berhasil disimpan ke SIMRS",
     *     "data": {
     *       "id_pasien": "PAT-456",
     *       "nik": "3215003456789012"
     *     }
     *   }
     */
    public function buat(Request $request)
    {
        // ============ STEP 1: VALIDASI TOKEN ============
        $validasi_result = $this->validasiTokenSimrs($request);
        if ($validasi_result !== true) {
            return $validasi_result;
        }

        // ============ STEP 2: VALIDASI INPUT ============
        // Pastikan semua data yang diperlukan sudah ada dan format benar
        $validated = $request->validate([
            'nik' => 'required|string|max:16',
            'nama_lengkap' => 'required|string|max:255',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'alamat' => 'required|string|max:500',
            'no_telepon' => 'required|string|max:20',
            'golongan_darah' => 'nullable|in:A,B,AB,O',
            'alergi' => 'nullable|array',
        ]);

        // ============ STEP 3: LOG DATA ============
        // Catat semua data ke log file untuk audit trail
        Log::info('Pasien baru disinkronisasi ke SIMRS', $validated);

        // ============ STEP 4: GENERATE ID PASIEN ============
        // Dalam implementasi real, database akan auto-generate ID
        $id_pasien_baru = 'PAT-' . rand(100, 999);

        // ============ STEP 5: RETURN RESPONSE SUCCESS ============
        return response()->json([
            'success' => true,
            'pesan' => 'Data pasien berhasil disimpan ke SIMRS',
            'data' => [
                'id_pasien' => $id_pasien_baru,
                'nik' => $validated['nik'],
                'nama_lengkap' => $validated['nama_lengkap'],
            ],
        ], 201);
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