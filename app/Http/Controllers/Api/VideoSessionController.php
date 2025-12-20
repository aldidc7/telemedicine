<?php

namespace App\Http\Controllers\Api;

use App\Models\VideoSession;
use App\Models\Consultation;
use App\Models\VideoParticipantLog;
use App\Models\VideoSessionEvent;
use App\Services\Video\VideoSessionService;
use App\Services\Video\JitsiTokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class VideoSessionController extends ApiController
{
    private VideoSessionService $videoSessionService;

    public function __construct()
    {
        $this->videoSessionService = new VideoSessionService();
    }

    /**
     * Initialize a video session
     * POST /api/v1/video-sessions
     * 
     * Required: consultation_id
     * Optional: room_id (will be generated if not provided)
     */
    public function create(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'consultation_id' => 'required|exists:konsultasis,id',
        ]);

        $userId = Auth::id();
        $consultation = Consultation::findOrFail($validated['consultation_id']);

        // Verify user is doctor or patient in this consultation
        if ($consultation->doctor_id !== $userId && $consultation->patient_id !== $userId) {
            return $this->error('Unauthorized', [], 403);
        }

        // Check if session already exists and is active
        $existingSession = VideoSession::where('consultation_id', $consultation->id)
            ->whereIn('status', ['pending', 'ringing', 'active'])
            ->first();

        if ($existingSession) {
            return $this->success([
                'session' => $existingSession->load(['doctor', 'patient']),
                'message' => 'Session sudah ada',
            ]);
        }

        // Create new session
        $roomId = Str::uuid()->toString();
        
        $session = VideoSession::create([
            'consultation_id' => $consultation->id,
            'doctor_id' => $consultation->doctor_id,
            'patient_id' => $consultation->patient_id,
            'room_id' => $roomId,
            'status' => VideoSession::STATUS_PENDING,
        ]);

        // Log initial event
        VideoSessionEvent::logEvent(
            $session->id,
            VideoSessionEvent::TYPE_INFO,
            'Video session diinisialisasi',
            ['initiated_by_id' => $userId]
        );

        return $this->success([
            'session' => $session->load(['doctor', 'patient']),
            'room_id' => $roomId,
        ], 'Video session berhasil dibuat', 201);
    }

    /**
     * Get video session details
     * GET /api/v1/video-sessions/{id}
     */
    public function show(string $id): JsonResponse
    {
        $session = VideoSession::findOrFail($id);
        $userId = Auth::id();

        // Verify user is part of this session
        if ($session->doctor_id !== $userId && $session->patient_id !== $userId) {
            return $this->error('Unauthorized', [], 403);
        }

        return $this->success([
            'session' => $session->load([
                'doctor:id,name,email,phone',
                'patient:id,name,email,phone',
                'participantLogs' => function ($q) {
                    $q->orderBy('timestamp', 'asc');
                },
                'events' => function ($q) {
                    $q->orderBy('timestamp', 'asc');
                }
            ]),
        ]);
    }

    /**
     * Start video session
     * POST /api/v1/video-sessions/{id}/start
     */
    public function start(string $id): JsonResponse
    {
        $session = VideoSession::findOrFail($id);
        $userId = Auth::id();

        // Only doctor can start
        if ($session->doctor_id !== $userId) {
            return $this->error('Hanya dokter yang dapat memulai session', [], 403);
        }

        if ($session->status !== VideoSession::STATUS_PENDING) {
            return $this->error('Session tidak dalam status pending', [], 400);
        }

        $session->start();

        // Log event
        VideoSessionEvent::logEvent(
            $session->id,
            VideoSessionEvent::TYPE_INFO,
            'Video session dimulai',
            ['started_by_id' => $userId]
        );

        return $this->success([
            'session' => $session->load(['doctor', 'patient']),
        ], 'Video session dimulai');
    }

    /**
     * End video session
     * POST /api/v1/video-sessions/{id}/end
     */
    public function end(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'nullable|string',
            'quality' => 'nullable|in:excellent,good,fair,poor',
            'notes' => 'nullable|string',
        ]);

        $session = VideoSession::findOrFail($id);
        $userId = Auth::id();

        // Verify user is part of session
        if ($session->doctor_id !== $userId && $session->patient_id !== $userId) {
            return $this->error('Unauthorized', [], 403);
        }

        if ($session->isEnded()) {
            return $this->error('Session sudah selesai', [], 400);
        }

        $reason = $validated['reason'] ?? VideoSession::REASON_USER_ENDED;
        $session->end($reason);

        if (isset($validated['quality'])) {
            $session->setCallQuality($validated['quality']);
        }

        if (isset($validated['notes'])) {
            $session->update(['notes' => $validated['notes']]);
        }

        // Log end event
        VideoSessionEvent::logEvent(
            $session->id,
            VideoSessionEvent::TYPE_INFO,
            'Video session berakhir',
            [
                'ended_by_id' => $userId,
                'reason' => $reason,
                'quality' => $validated['quality'] ?? null,
            ]
        );

        return $this->success([
            'session' => $session->load(['doctor', 'patient']),
        ], 'Video session berakhir');
    }

    /**
     * Log participant activity
     * POST /api/v1/video-sessions/{id}/log-event
     */
    public function logParticipantEvent(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'event_type' => 'required|string',
            'ip_address' => 'nullable|ip',
            'device_type' => 'nullable|in:desktop,laptop,tablet,mobile',
            'browser_info' => 'nullable|string',
            'connection_quality' => 'nullable|in:excellent,good,fair,poor',
            'audio_enabled' => 'nullable|boolean',
            'video_enabled' => 'nullable|boolean',
            'screen_shared' => 'nullable|boolean',
        ]);

        $session = VideoSession::findOrFail($id);
        $userId = Auth::id();

        // Create participant log
        VideoParticipantLog::createLog($session->id, $userId, $validated['event_type'], $validated);

        // If network degraded, log as event
        if ($validated['event_type'] === 'network_degraded') {
            VideoSessionEvent::logEvent(
                $session->id,
                VideoSessionEvent::TYPE_WARNING,
                'Jaringan peserta menurun',
                ['user_id' => $userId, 'quality' => $validated['connection_quality'] ?? null],
                VideoSessionEvent::SEVERITY_MEDIUM
            );
        }

        return $this->success([], 'Event berhasil dicatat');
    }

    /**
     * Upload recording
     * POST /api/v1/video-sessions/{id}/upload-recording
     */
    public function uploadRecording(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'recording_url' => 'required|url',
            'size_bytes' => 'nullable|integer|min:0',
        ]);

        $session = VideoSession::findOrFail($id);
        $userId = Auth::id();

        // Only doctor can upload
        if ($session->doctor_id !== $userId) {
            return $this->error('Hanya dokter yang dapat upload recording', [], 403);
        }

        $session->markRecorded($validated['recording_url'], $validated['size_bytes'] ?? null);

        // Log event
        VideoSessionEvent::logEvent(
            $session->id,
            VideoSessionEvent::TYPE_INFO,
            'Recording berhasil diupload',
            ['size' => $validated['size_bytes'] ?? null]
        );

        return $this->success([
            'session' => $session->load(['doctor', 'patient']),
        ], 'Recording berhasil diupload');
    }

    /**
     * Get session analytics
     * GET /api/v1/video-sessions/{id}/analytics
     */
    public function analytics(string $id): JsonResponse
    {
        $session = VideoSession::findOrFail($id);
        $userId = Auth::id();

        if ($session->doctor_id !== $userId && $session->patient_id !== $userId) {
            return $this->error('Unauthorized', [], 403);
        }

        // Get participant logs
        $doctorLogs = VideoParticipantLog::where('video_session_id', $id)
            ->where('user_id', $session->doctor_id)
            ->orderBy('timestamp')
            ->get();

        $patientLogs = VideoParticipantLog::where('video_session_id', $id)
            ->where('user_id', $session->patient_id)
            ->orderBy('timestamp')
            ->get();

        // Get events
        $events = VideoSessionEvent::where('video_session_id', $id)
            ->orderBy('timestamp')
            ->get();

        // Calculate stats
        $doctorJoined = $doctorLogs->where('event_type', 'joined')->first();
        $doctorLeft = $doctorLogs->where('event_type', 'left')->last();
        $patientJoined = $patientLogs->where('event_type', 'joined')->first();
        $patientLeft = $patientLogs->where('event_type', 'left')->last();

        return $this->success([
            'session_id' => $session->id,
            'duration_formatted' => $session->getDurationFormatted(),
            'call_quality' => $session->call_quality,
            'recording_size' => $session->getRecordingSizeFormatted(),
            'doctor' => [
                'id' => $session->doctor_id,
                'name' => $session->doctor->name,
                'joined_at' => $doctorJoined?->timestamp,
                'left_at' => $doctorLeft?->timestamp,
                'connection_quality' => $doctorLogs->last()?->connection_quality,
            ],
            'patient' => [
                'id' => $session->patient_id,
                'name' => $session->patient->name,
                'joined_at' => $patientJoined?->timestamp,
                'left_at' => $patientLeft?->timestamp,
                'connection_quality' => $patientLogs->last()?->connection_quality,
            ],
            'events_count' => $events->count(),
            'warnings_count' => $events->where('severity', 'high')->count(),
            'errors_count' => $events->where('event_type', 'error')->count(),
        ]);
    }

    /**
     * List user's video sessions
     * GET /api/v1/video-sessions
     */
    public function index(Request $request): JsonResponse
    {
        $userId = Auth::id();

        $sessions = VideoSession::where(function ($q) use ($userId) {
            $q->where('doctor_id', $userId)
              ->orWhere('patient_id', $userId);
        })
        ->orderBy('created_at', 'desc')
        ->paginate(15);

        return $this->success([
            'sessions' => $sessions->items(),
            'pagination' => [
                'total' => $sessions->total(),
                'per_page' => $sessions->perPage(),
                'current_page' => $sessions->currentPage(),
                'last_page' => $sessions->lastPage(),
            ],
        ]);
    }

    /**
     * Get Jitsi JWT Token untuk video session
     * GET /api/v1/video-sessions/{id}/jitsi-token
     * 
     * Returns JWT token untuk authentikasi ke Jitsi server
     */
    public function getJitsiToken(string $id): JsonResponse
    {
        try {
            $userId = Auth::id();
            $session = VideoSession::findOrFail($id);

            // Verify user is part of session
            if ($session->doctor_id !== $userId && $session->patient_id !== $userId) {
                return $this->error('Unauthorized', [], 403);
            }

            // Get Jitsi token
            $token = $this->videoSessionService->getJitsiToken($session->id, $userId);

            // Get Jitsi config
            $config = $this->videoSessionService->getJitsiConfig();

            // Get room name
            $roomName = \App\Services\Video\JitsiTokenService::formatRoomName($session->consultation_id);

            return $this->success([
                'token' => $token,
                'room_name' => $roomName,
                'server_url' => $config['server_url'],
                'session_id' => $session->id,
                'consultation_id' => $session->consultation_id,
                'is_doctor' => $session->doctor_id === $userId,
                'participant_name' => Auth::check() ? Auth::user()->name : 'Participant',
            ], 'Jitsi token berhasil dibuat');
        } catch (\Exception $e) {
            return $this->error('Gagal membuat Jitsi token: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Get Jitsi Configuration
     * GET /api/v1/video-sessions/config/jitsi
     * 
     * Returns Jitsi configuration untuk frontend
     */
    public function getJitsiConfig(): JsonResponse
    {
        try {
            $config = $this->videoSessionService->getJitsiConfig();

            return $this->success([
                'config' => $config,
            ], 'Jitsi config berhasil diambil');
        } catch (\Exception $e) {
            return $this->error('Gagal mengambil Jitsi config: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Log Jitsi Event
     * POST /api/v1/video-sessions/{id}/jitsi-event
     * 
     * Log event dari Jitsi (participant joined, left, etc)
     */
    public function logJitsiEvent(Request $request, string $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'event_type' => 'required|string',
                'timestamp' => 'nullable|date',
                'data' => 'nullable|array',
                'connection_quality' => 'nullable|in:excellent,good,fair,poor',
                'audio_enabled' => 'nullable|boolean',
                'video_enabled' => 'nullable|boolean',
            ]);

            $userId = Auth::id();
            $session = VideoSession::findOrFail($id);

            // Verify user is part of session
            if ($session->doctor_id !== $userId && $session->patient_id !== $userId) {
                return $this->error('Unauthorized', [], 403);
            }

            // Log participant event
            $this->videoSessionService->logParticipantEvent(
                $session->id,
                $userId,
                $validated['event_type'],
                $validated
            );

            // Log session event if network degraded
            if ($validated['event_type'] === 'network_degraded') {
                VideoSessionEvent::logEvent(
                    $session->id,
                    VideoSessionEvent::TYPE_WARNING,
                    'Kualitas jaringan menurun',
                    [
                        'user_id' => $userId,
                        'quality' => $validated['connection_quality'] ?? null
                    ],
                    VideoSessionEvent::SEVERITY_MEDIUM
                );
            } elseif ($validated['event_type'] === 'connection_error') {
                VideoSessionEvent::logEvent(
                    $session->id,
                    VideoSessionEvent::TYPE_ERROR,
                    'Kesalahan koneksi',
                    ['user_id' => $userId],
                    VideoSessionEvent::SEVERITY_HIGH
                );
            }

            return $this->success([], 'Event berhasil dicatat');
        } catch (\Exception $e) {
            return $this->error('Gagal mencatat event: ' . $e->getMessage(), [], 500);
        }
    }
}
