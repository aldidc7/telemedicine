<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Konsultasi;
use App\Models\VideoRecording;
use App\Models\VideoRecordingConsent;
use App\Services\Video\JitsiTokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * VideoCallController handles video consultation operations
 * 
 * Features:
 * - JWT token generation for Jitsi integration
 * - Recording consent management (GDPR compliance)
 * - Recording start/stop lifecycle
 * - Consultation session management
 * - Recording download and deletion
 * @noinspection PhpUndefinedMethodInspection
 */
class VideoCallController extends Controller
{
    protected JitsiTokenService $jitsiTokenService;

    /**
     * Constructor dengan middleware registration
     */
    public function __construct(JitsiTokenService $jitsiTokenService)
    {
        $this->jitsiTokenService = $jitsiTokenService;
        $this->middleware('auth:sanctum');
    }

    /**
     * Initiate video consultation session
     * Generates JWT token and creates consultation record
     *
     * @param Request $request
     * @param int $consultationId
     * @return JsonResponse
     */
    public function startConsultation(Request $request, int $consultationId): JsonResponse
    {
        try {
            $konsultasi = Konsultasi::findOrFail($consultationId);
            $user = Auth::user();

            // Check user is either doctor or patient in this consultation
            $isDoctor = $user->id === $konsultasi->doctor_id && $user->hasRole('doctor');
            $isPatient = $user->id === $konsultasi->patient_id && $user->hasRole('patient');

            if (!$isDoctor && !$isPatient) {
                return response()->json(
                    ['message' => 'Unauthorized to access this consultation'],
                    403
                );
            }

            // Generate JWT token for Jitsi
            $roomName = $this->generateRoomName($consultationId);
            $jwtToken = $this->jitsiTokenService->generateToken(
                userId: $user->id,
                userName: $user->name,
                userEmail: $user->email,
                roomName: $roomName,
                userRole: $isDoctor ? 'doctor' : 'patient'
            );

            // Update consultation status
            $konsultasi->update([
                'status' => 'ongoing',
                'started_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'room_name' => $roomName,
                    'jwt_token' => $jwtToken,
                    'consultation_id' => $consultationId,
                    'user_name' => $user->name,
                    'user_email' => $user->email,
                    'is_doctor' => $isDoctor,
                ],
                'message' => 'Consultation started successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json(
                ['message' => 'Failed to start consultation: ' . $e->getMessage()],
                500
            );
        }
    }

    /**
     * Handle recording consent before video session
     * Creates VideoRecordingConsent record for GDPR compliance
     *
     * @param Request $request
     * @param int $consultationId
     * @return JsonResponse
     */
    public function storeConsent(Request $request, int $consultationId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'consented_to_recording' => 'required|boolean',
                'consent_reason' => 'nullable|string|max:255',
                'ip_address' => 'nullable|ip',
                'user_agent' => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $konsultasi = Konsultasi::findOrFail($consultationId);
            $user = Auth::user();

            // Ensure user is patient or doctor
            if ($user->id !== $konsultasi->patient_id && $user->id !== $konsultasi->doctor_id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            // Create or update consent record
            $consent = VideoRecordingConsent::updateOrCreate(
                [
                    'consultation_id' => $consultationId,
                    'patient_id' => $konsultasi->patient_id,
                    'doctor_id' => $konsultasi->doctor_id,
                ],
                [
                    'consented_to_recording' => $request->get('consented_to_recording'),
                    'consent_reason' => $request->get('consent_reason'),
                    'ip_address' => $request->get('ip_address', request()->ip()),
                    'user_agent' => $request->get('user_agent', request()->header('User-Agent')),
                    'consent_given_at' => now(),
                ]
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'consent_id' => $consent->id,
                    'consented_to_recording' => $consent->consented_to_recording,
                    'status' => $consent->getStatusText(),
                ],
                'message' => 'Consent recorded successfully',
            ], 201);
        } catch (\Exception $e) {
            return response()->json(
                ['message' => 'Failed to store consent: ' . $e->getMessage()],
                500
            );
        }
    }

    /**
     * Start recording in Jitsi
     * Logs recording start event in database
     *
     * @param Request $request
     * @param int $consultationId
     * @return JsonResponse
     */
    public function recordingStart(Request $request, int $consultationId): JsonResponse
    {
        try {
            $konsultasi = Konsultasi::findOrFail($consultationId);
            $user = Auth::user();

            // Check permissions
            if ($user->id !== $konsultasi->doctor_id && $user->id !== $konsultasi->patient_id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            // Check consent was given
            $consent = VideoRecordingConsent::where('consultation_id', $consultationId)->first();
            if (!$consent || !$consent->consented_to_recording) {
                return response()->json(
                    ['message' => 'Recording consent not found or denied'],
                    403
                );
            }

            // Create VideoRecording record
            $recording = VideoRecording::create([
                'consultation_id' => $consultationId,
                'doctor_id' => $konsultasi->doctor_id,
                'patient_id' => $konsultasi->patient_id,
                'jitsi_room_name' => $this->generateRoomName($consultationId),
                'storage_path' => null, // Will be set when recording is saved
                'duration' => 0,
                'file_size' => 0,
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'recording_id' => $recording->id,
                    'started_at' => $recording->created_at,
                ],
                'message' => 'Recording started',
            ], 200);
        } catch (\Exception $e) {
            return response()->json(
                ['message' => 'Failed to start recording: ' . $e->getMessage()],
                500
            );
        }
    }

    /**
     * Stop recording and save metadata
     *
     * @param Request $request
     * @param int $consultationId
     * @return JsonResponse
     */
    public function recordingStop(Request $request, int $consultationId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'recording_id' => 'required|integer|exists:video_recordings,id',
                'duration' => 'required|integer|min:0',
                'file_size' => 'nullable|integer|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $recording = VideoRecording::findOrFail($request->get('recording_id'));
            $user = Auth::user();

            // Verify user owns this recording
            if ($user->id !== $recording->doctor_id && $user->id !== $recording->patient_id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            // Update recording with duration and file size
            $recording->update([
                'duration' => $request->get('duration'),
                'file_size' => $request->get('file_size', 0),
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'recording_id' => $recording->id,
                    'duration_formatted' => $recording->getDurationFormatted(),
                    'file_size_formatted' => $recording->getFileSizeFormatted(),
                ],
                'message' => 'Recording stopped and saved',
            ], 200);
        } catch (\Exception $e) {
            return response()->json(
                ['message' => 'Failed to stop recording: ' . $e->getMessage()],
                500
            );
        }
    }

    /**
     * End consultation session
     * Updates status and cleans up resources
     *
     * @param Request $request
     * @param int $consultationId
     * @return JsonResponse
     */
    public function endConsultation(Request $request, int $consultationId): JsonResponse
    {
        try {
            $konsultasi = Konsultasi::findOrFail($consultationId);
            $user = Auth::user();

            // Verify user is doctor or patient
            if ($user->id !== $konsultasi->doctor_id && $user->id !== $konsultasi->patient_id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            // Update consultation status
            $konsultasi->update([
                'status' => 'completed',
                'ended_at' => now(),
                'duration_minutes' => $request->get('duration', 0),
            ]);

            // Get associated recording if exists
            $recording = VideoRecording::where('consultation_id', $consultationId)->first();

            return response()->json([
                'success' => true,
                'data' => [
                    'consultation_id' => $consultationId,
                    'status' => $konsultasi->status,
                    'ended_at' => $konsultasi->ended_at,
                    'recording_id' => $recording?->id,
                ],
                'message' => 'Consultation ended successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json(
                ['message' => 'Failed to end consultation: ' . $e->getMessage()],
                500
            );
        }
    }

    /**
     * Get list of recordings for authenticated user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function listRecordings(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $perPage = $request->get('per_page', 15);

            $recordings = VideoRecording::where(function ($query) use ($user) {
                $query->where('doctor_id', $user->id)
                    ->orWhere('patient_id', $user->id);
            })
                ->with(['konsultasi', 'doctor', 'patient'])
                ->whereNull('deleted_at')
                ->latest()
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $recordings->map(fn($r) => [
                    'id' => $r->id,
                    'consultation_id' => $r->consultation_id,
                    'duration' => $r->duration,
                    'duration_formatted' => $r->getDurationFormatted(),
                    'file_size_formatted' => $r->getFileSizeFormatted(),
                    'created_at' => $r->created_at,
                    'doctor_name' => $r->doctor->name,
                    'patient_name' => $r->patient->name,
                ]),
                'pagination' => [
                    'total' => $recordings->total(),
                    'per_page' => $recordings->perPage(),
                    'current_page' => $recordings->currentPage(),
                    'last_page' => $recordings->lastPage(),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json(
                ['message' => 'Failed to retrieve recordings: ' . $e->getMessage()],
                500
            );
        }
    }

    /**
     * Download recording file
     *
     * @param int $recordingId
     * @return StreamedResponse|JsonResponse
     */
    public function downloadRecording(int $recordingId): StreamedResponse|JsonResponse
    {
        try {
            $recording = VideoRecording::findOrFail($recordingId);
            $user = Auth::user();

            // Verify access
            if (
                !$recording->isAccessible() ||
                ($user->id !== $recording->doctor_id && $user->id !== $recording->patient_id)
            ) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            // Check file exists
            if (!Storage::disk('videos')->exists($recording->storage_path)) {
                return response()->json(['message' => 'File not found'], 404);
            }

            return Storage::disk('videos')->download($recording->storage_path);
        } catch (\Exception $e) {
            return response()->json(
                ['message' => 'Failed to download recording: ' . $e->getMessage()],
                500
            );
        }
    }

    /**
     * Get recording details
     *
     * @param int $recordingId
     * @return JsonResponse
     */
    public function getRecording(int $recordingId): JsonResponse
    {
        try {
            $recording = VideoRecording::with(['konsultasi', 'doctor', 'patient'])->findOrFail($recordingId);
            $user = Auth::user();

            // Verify access
            if ($user->id !== $recording->doctor_id && $user->id !== $recording->patient_id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $recording->id,
                    'consultation_id' => $recording->consultation_id,
                    'duration' => $recording->duration,
                    'duration_formatted' => $recording->getDurationFormatted(),
                    'file_size' => $recording->file_size,
                    'file_size_formatted' => $recording->getFileSizeFormatted(),
                    'is_accessible' => $recording->isAccessible(),
                    'created_at' => $recording->created_at,
                    'doctor' => [
                        'id' => $recording->doctor->id,
                        'name' => $recording->doctor->name,
                    ],
                    'patient' => [
                        'id' => $recording->patient->id,
                        'name' => $recording->patient->name,
                    ],
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json(
                ['message' => 'Failed to retrieve recording: ' . $e->getMessage()],
                500
            );
        }
    }

    /**
     * Soft delete recording
     *
     * @param int $recordingId
     * @return JsonResponse
     */
    public function deleteRecording(int $recordingId): JsonResponse
    {
        try {
            $recording = VideoRecording::findOrFail($recordingId);
            $user = Auth::user();

            // Only patient can delete
            if ($user->id !== $recording->patient_id) {
                return response()->json(
                    ['message' => 'Only patient can delete recording'],
                    403
                );
            }

            $recording->markAsDeleted();

            return response()->json([
                'success' => true,
                'message' => 'Recording deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json(
                ['message' => 'Failed to delete recording: ' . $e->getMessage()],
                500
            );
        }
    }

    /**
     * Withdraw recording consent
     *
     * @param int $consultationId
     * @return JsonResponse
     */
    public function withdrawConsent(int $consultationId): JsonResponse
    {
        try {
            $consent = VideoRecordingConsent::where('consultation_id', $consultationId)->firstOrFail();
            $user = Auth::user();

            // Only patient can withdraw
            if ($user->id !== $consent->patient_id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $consent->withdraw();

            return response()->json([
                'success' => true,
                'message' => 'Consent withdrawn successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json(
                ['message' => 'Failed to withdraw consent: ' . $e->getMessage()],
                500
            );
        }
    }

    /**
     * Generate unique room name for Jitsi
     *
     * @param int $consultationId
     * @return string
     */
    protected function generateRoomName(int $consultationId): string
    {
        return 'konsultasi-' . $consultationId . '-' . now()->format('YmdHis');
    }
}
