<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DoctorCredential;
use App\Models\DoctorVerification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DoctorCredentialVerificationController extends Controller
{
    /**
     * Submit credentials for verification
     * POST /api/v1/doctors/credentials/submit
     */
    public function submitCredentials(Request $request)
    {
        try {
            if (Auth::user()->role !== 'dokter') {
                return response()->json(['error' => 'Hanya dokter yang dapat submit credentials'], 403);
            }

            $validated = $request->validate([
                'credentials' => 'required|array|min:1',
                'credentials.*.type' => 'required|in:kki,sip,aipki,spesialis,subspesialis',
                'credentials.*.number' => 'required|string|unique_for_doctor',
                'credentials.*.issued_date' => 'required|date|before:expiry_date',
                'credentials.*.expiry_date' => 'required|date|after:issued_date',
                'credentials.*.document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
                'credentials.*.issued_by' => 'nullable|string',
            ]);

            $credentials = [];

            foreach ($validated['credentials'] as $cred) {
                $documentUrl = null;

                // Handle file upload
                if ($request->hasFile("credentials.*.document")) {
                    $file = $request->file("credentials.*.document");
                    $path = "credentials/doctors/" . Auth::id() . "/" . time() . "_" . $file->getClientOriginalName();
                    $documentUrl = Storage::disk('public')->putFileAs('credentials', $file, $path);
                }

                // Create or update credential
                $credential = DoctorCredential::updateOrCreate(
                    [
                        'doctor_id' => Auth::id(),
                        'credential_type' => $cred['type'],
                        'credential_number' => $cred['number'],
                    ],
                    [
                        'issued_date' => $cred['issued_date'],
                        'expiry_date' => $cred['expiry_date'],
                        'issued_by' => $cred['issued_by'] ?? null,
                        'document_url' => $documentUrl,
                        'status' => DoctorCredential::STATUS_PENDING,
                    ]
                );

                $credentials[] = [
                    'id' => $credential->id,
                    'type' => $credential->getTypeLabel(),
                    'number' => $credential->credential_number,
                    'status' => $credential->getStatusLabel(),
                ];
            }

            // Update or create verification record
            $verification = DoctorVerification::updateOrCreate(
                ['doctor_id' => Auth::id()],
                ['verification_status' => DoctorVerification::STATUS_PENDING]
            );

            return response()->json([
                'message' => 'Credentials berhasil disubmit untuk verifikasi',
                'verification_id' => $verification->id,
                'credentials' => $credentials,
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get doctor's credentials status
     * GET /api/v1/doctors/credentials/status
     */
    public function getCredentialsStatus()
    {
        try {
            if (Auth::user()->role !== 'dokter') {
                return response()->json(['error' => 'Hanya dokter yang dapat melihat status'], 403);
            }

            $verification = DoctorVerification::where('doctor_id', Auth::id())->first();

            if (!$verification) {
                return response()->json([
                    'message' => 'Belum ada submission credentials',
                    'verification_status' => 'unverified',
                    'credentials' => [],
                ]);
            }

            $credentials = DoctorCredential::forDoctor(Auth::id())
                ->get()
                ->map(function ($cred) {
                    return [
                        'id' => $cred->id,
                        'type' => $cred->getTypeLabel(),
                        'number' => $cred->credential_number,
                        'issued_date' => $cred->issued_date->format('Y-m-d'),
                        'expiry_date' => $cred->expiry_date->format('Y-m-d'),
                        'status' => $cred->getStatusLabel(),
                        'verified_at' => $cred->verified_at ? $cred->verified_at->format('Y-m-d H:i') : null,
                        'rejection_reason' => $cred->rejection_reason,
                    ];
                });

            return response()->json([
                'verification_id' => $verification->id,
                'verification_status' => $verification->getStatusLabel(),
                'is_verified' => $verification->isVerified(),
                'verified_at' => $verification->verified_at ? $verification->verified_at->format('Y-m-d H:i') : null,
                'credentials' => $credentials,
                'notes' => $verification->notes,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Admin: Get pending verifications
     * GET /api/v1/admin/verifications/pending
     */
    public function getPendingVerifications(Request $request)
    {
        try {
            if (Auth::user()->role !== 'admin') {
                return response()->json(['error' => 'Hanya admin yang dapat melihat list'], 403);
            }

            $page = $request->get('page', 1);
            $perPage = $request->get('per_page', 20);

            $verifications = DoctorVerification::pending()
                ->with('doctor')
                ->paginate($perPage, ['*'], 'page', $page);

            $data = $verifications->map(function ($v) {
                return [
                    'id' => $v->id,
                    'doctor_id' => $v->doctor_id,
                    'doctor_name' => $v->doctor->nama,
                    'status' => $v->getStatusLabel(),
                    'submitted_at' => $v->created_at->format('Y-m-d H:i'),
                    'credential_count' => $v->credentials()->count(),
                ];
            });

            return response()->json([
                'data' => $data,
                'pagination' => [
                    'total' => $verifications->total(),
                    'per_page' => $verifications->perPage(),
                    'current_page' => $verifications->currentPage(),
                    'last_page' => $verifications->lastPage(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Admin: Get verification detail
     * GET /api/v1/admin/verifications/{id}
     */
    public function getVerificationDetail($verificationId)
    {
        try {
            if (Auth::user()->role !== 'admin') {
                return response()->json(['error' => 'Hanya admin yang dapat melihat detail'], 403);
            }

            $verification = DoctorVerification::with('doctor', 'credentials')->find($verificationId);

            if (!$verification) {
                return response()->json(['error' => 'Verifikasi tidak ditemukan'], 404);
            }

            $credentials = $verification->credentials()
                ->get()
                ->map(function ($cred) {
                    return [
                        'id' => $cred->id,
                        'type' => $cred->getTypeLabel(),
                        'number' => $cred->credential_number,
                        'issued_date' => $cred->issued_date->format('Y-m-d'),
                        'expiry_date' => $cred->expiry_date->format('Y-m-d'),
                        'issued_by' => $cred->issued_by,
                        'document_url' => $cred->document_url,
                        'status' => $cred->getStatusLabel(),
                        'notes' => $cred->notes,
                    ];
                });

            return response()->json([
                'id' => $verification->id,
                'doctor' => [
                    'id' => $verification->doctor->id,
                    'name' => $verification->doctor->nama,
                    'email' => $verification->doctor->email,
                    'phone' => $verification->doctor->nomor_telepon,
                ],
                'status' => $verification->getStatusLabel(),
                'credentials' => $credentials,
                'submitted_at' => $verification->created_at->format('Y-m-d H:i'),
                'notes' => $verification->notes,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Admin: Verify credentials
     * POST /api/v1/admin/verifications/{id}/verify
     */
    public function verifyCredentials($verificationId, Request $request)
    {
        try {
            if (Auth::user()->role !== 'admin') {
                return response()->json(['error' => 'Hanya admin yang dapat verify'], 403);
            }

            $validated = $request->validate([
                'credential_ids' => 'required|array',
                'notes' => 'nullable|string',
            ]);

            $verification = DoctorVerification::find($verificationId);
            if (!$verification) {
                return response()->json(['error' => 'Verifikasi tidak ditemukan'], 404);
            }

            // Update each credential
            foreach ($validated['credential_ids'] as $credentialId) {
                $credential = DoctorCredential::find($credentialId);
                if ($credential && $credential->doctor_id === $verification->doctor_id) {
                    $credential->verify(Auth::id());
                }
            }

            // Check if all required credentials are verified
            if ($verification->hasAllRequiredCredentials()) {
                $verification->verify(Auth::id(), $validated['notes'] ?? null);
            }

            return response()->json([
                'message' => 'Credentials berhasil diverifikasi',
                'verification_id' => $verification->id,
                'status' => $verification->getStatusLabel(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Admin: Reject credentials
     * POST /api/v1/admin/verifications/{id}/reject
     */
    public function rejectCredentials($verificationId, Request $request)
    {
        try {
            if (Auth::user()->role !== 'admin') {
                return response()->json(['error' => 'Hanya admin yang dapat reject'], 403);
            }

            $validated = $request->validate([
                'credential_id' => 'required|exists:doctor_credentials,id',
                'reason' => 'required|string|min:10',
            ]);

            $verification = DoctorVerification::find($verificationId);
            if (!$verification) {
                return response()->json(['error' => 'Verifikasi tidak ditemukan'], 404);
            }

            $credential = DoctorCredential::find($validated['credential_id']);
            if ($credential && $credential->doctor_id === $verification->doctor_id) {
                $credential->reject($validated['reason']);
            }

            return response()->json([
                'message' => 'Credential ditolak',
                'credential_id' => $credential->id,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Admin: Approve full verification
     * POST /api/v1/admin/verifications/{id}/approve
     */
    public function approveVerification($verificationId, Request $request)
    {
        try {
            if (Auth::user()->role !== 'admin') {
                return response()->json(['error' => 'Hanya admin yang dapat approve'], 403);
            }

            $validated = $request->validate([
                'notes' => 'nullable|string',
            ]);

            $verification = DoctorVerification::find($verificationId);
            if (!$verification) {
                return response()->json(['error' => 'Verifikasi tidak ditemukan'], 404);
            }

            $verification->verify(Auth::id(), $validated['notes'] ?? null);

            return response()->json([
                'message' => 'Dokter berhasil diverifikasi',
                'doctor_id' => $verification->doctor_id,
                'status' => $verification->getStatusLabel(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get doctor verification public info
     * GET /api/v1/doctors/{id}/verification
     */
    public function getDoctorVerificationStatus($doctorId)
    {
        try {
            $doctor = User::find($doctorId);
            if (!$doctor || $doctor->role !== 'dokter') {
                return response()->json(['error' => 'Dokter tidak ditemukan'], 404);
            }

            $verification = DoctorVerification::where('doctor_id', $doctorId)->first();

            if (!$verification) {
                return response()->json([
                    'is_verified' => false,
                    'status' => 'unverified',
                ]);
            }

            return response()->json([
                'is_verified' => $verification->isVerified(),
                'status' => $verification->getStatusLabel(),
                'verified_at' => $verification->verified_at ? $verification->verified_at->format('Y-m-d') : null,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
