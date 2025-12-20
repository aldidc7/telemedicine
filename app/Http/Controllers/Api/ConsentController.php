<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ConsentRecord;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * CONSENT CONTROLLER
 * 
 * Mengelola informed consent pengguna untuk telemedicine
 * Sesuai dengan regulasi:
 * - India Telemedicine Practice Guidelines 2020
 * - Ryan Haight Act (valid consultation required)
 * - WHO Telemedicine Standards
 * - Indonesia Health Law 36/2009
 */
class ConsentController extends Controller
{
    /**
     * Dapatkan consent yang diperlukan untuk user
     * GET /api/v1/consent/required
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRequired(Request $request)
    {
        try {
            $user = $request->user();

            // Definisikan consent types yang WAJIB
            $requiredConsents = [
                'telemedicine' => [
                    'name' => 'Persetujuan Telemedicine',
                    'description' => 'Saya memahami risiko dan keuntungan telemedicine dan menyetujuinya',
                    'order' => 1,
                    'mandatory' => true,
                ],
                'privacy_policy' => [
                    'name' => 'Kebijakan Privasi',
                    'description' => 'Saya telah membaca dan menyetujui kebijakan privasi data saya',
                    'order' => 2,
                    'mandatory' => true,
                ],
                'data_handling' => [
                    'name' => 'Penanganan Data Medis',
                    'description' => 'Saya menyetujui data medis saya disimpan dan diproses sesuai regulasi',
                    'order' => 3,
                    'mandatory' => true,
                ],
            ];

            // Periksa consent status untuk setiap tipe
            $consentStatus = [];
            foreach ($requiredConsents as $type => $details) {
                $consentStatus[$type] = [
                    ...array_slice($details, 0, -1), // Exclude 'mandatory' dari response
                    'accepted' => ConsentRecord::hasValidConsent($user->id, $type),
                ];
            }

            // Hitung berapa banyak consent yang sudah diterima
            $acceptedCount = count(array_filter($consentStatus, fn($c) => $c['accepted']));
            $totalRequired = count($consentStatus);

            return response()->json([
                'success' => true,
                'data' => [
                    'consents' => $consentStatus,
                    'accepted_count' => $acceptedCount,
                    'total_required' => $totalRequired,
                    'all_consents_accepted' => $acceptedCount === $totalRequired,
                    'user_id' => $user->id,
                    'checked_at' => now(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data consent: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Catat consent yang diterima user
     * POST /api/v1/consent/accept
     * 
     * Body:
     * {
     *     "consent_type": "telemedicine|privacy_policy|data_handling",
     *     "accepted": true
     * }
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function accept(Request $request)
    {
        try {
            $validated = $request->validate([
                'consent_type' => 'required|string|in:telemedicine,privacy_policy,data_handling',
                'accepted' => 'required|boolean|accepted',
            ]);

            $user = $request->user();

            // Catat consent
            $consent = ConsentRecord::recordConsent(
                userId: $user->id,
                consentType: $validated['consent_type'],
                consentText: $this->getConsentText($validated['consent_type']),
                ipAddress: $request->ip(),
                userAgent: $request->header('User-Agent'),
            );

            // Log ke ActivityLog
            activity()
                ->causedBy($user)
                ->performedOn($consent)
                ->withProperties(['consent_type' => $validated['consent_type']])
                ->log('Accepted ' . $validated['consent_type'] . ' consent');

            return response()->json([
                'success' => true,
                'message' => 'Consent berhasil dicatat',
                'data' => [
                    'consent_id' => $consent->id,
                    'consent_type' => $consent->consent_type,
                    'accepted_at' => $consent->accepted_at,
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mencatat consent: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cek apakah user memiliki consent valid
     * GET /api/v1/consent/check/{type}
     * 
     * @param string $type
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function check($type, Request $request)
    {
        try {
            $user = $request->user();

            $hasConsent = ConsentRecord::hasValidConsent($user->id, $type);

            return response()->json([
                'success' => true,
                'data' => [
                    'has_valid_consent' => $hasConsent,
                    'consent_type' => $type,
                    'user_id' => $user->id,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memeriksa consent: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Dapatkan riwayat consent user
     * GET /api/v1/consent/history
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function history(Request $request)
    {
        try {
            $user = $request->user();

            $consents = ConsentRecord::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->select([
                    'id',
                    'consent_type',
                    'accepted',
                    'accepted_at',
                    'ip_address',
                    'created_at',
                    'revoked_at',
                ])
                ->get()
                ->map(function ($consent) {
                    return [
                        'id' => $consent->id,
                        'type' => $consent->consent_type,
                        'status' => $consent->accepted ? 'accepted' : 'pending',
                        'accepted_at' => $consent->accepted_at,
                        'created_at' => $consent->created_at,
                        'is_active' => $consent->revoked_at === null,
                        'ip_address' => $consent->ip_address,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'consents' => $consents,
                    'total_consents' => $consents->count(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil riwayat consent: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Tarik kembali (revoke) consent
     * POST /api/v1/consent/revoke/{id}
     * 
     * @param string $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function revoke($id, Request $request)
    {
        try {
            $user = $request->user();

            $consent = ConsentRecord::where('id', $id)
                ->where('user_id', $user->id)
                ->firstOrFail();

            $consent->revoke();

            // Log ke ActivityLog
            activity()
                ->causedBy($user)
                ->performedOn($consent)
                ->withProperties(['reason' => 'User revoked consent'])
                ->log('Revoked ' . $consent->consent_type . ' consent');

            return response()->json([
                'success' => true,
                'message' => 'Consent berhasil ditarik kembali',
                'data' => [
                    'consent_id' => $consent->id,
                    'revoked_at' => $consent->revoked_at,
                ],
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Consent tidak ditemukan',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mencabut consent: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Helper: Dapatkan teks consent lengkap
     * 
     * @param string $type
     * @return string
     */
    private function getConsentText($type)
    {
        $texts = [
            'telemedicine' => 'Saya memahami dan menyetujui untuk menggunakan layanan telemedicine dengan dokter yang tersedia. Saya menyadari bahwa: (1) Telemedicine mungkin tidak cocok untuk semua kondisi medis; (2) Saya memiliki pilihan untuk berkonsultasi tatap muka; (3) Saya dapat menghentikan layanan kapan saja; (4) Data saya akan dienkripsi dan diproteksi sesuai regulasi.',
            'privacy_policy' => 'Saya telah membaca dan memahami Kebijakan Privasi aplikasi telemedicine ini. Saya menyetujui bahwa data pribadi dan medis saya diproses sesuai dengan kebijakan tersebut, termasuk: (1) Pengumpulan data medis untuk diagnosis dan perawatan; (2) Berbagi data dengan dokter dan penyedia kesehatan terlibat; (3) Penyimpanan data selama 7-10 tahun sesuai regulasi; (4) Penggunaan data untuk peningkatan layanan dengan anonimisasi.',
            'data_handling' => 'Saya menyetujui bahwa data medis saya: (1) Disimpan dalam database terenkripsi yang aman; (2) Diakses hanya oleh tenaga medis yang berwenang; (3) Dilindungi dengan standar keamanan internasional (HIPAA-equivalent); (4) Dapat diaudit untuk kepatuhan regulasi; (5) Dapat saya akses, koreksi, atau minta copy kapan saja.',
        ];

        return $texts[$type] ?? '';
    }
}
