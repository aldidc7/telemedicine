<?php

namespace App\Services\Video;

use App\Models\VideoSession;
use App\Models\Consultation;
use App\Models\VideoSessionEvent;
use App\Models\VideoParticipantLog;
use Illuminate\Support\Str;
use Exception;

/**
 * Video Session Service
 * 
 * Service untuk manage video session lifecycle dan Jitsi integration
 */
class VideoSessionService
{
    private JitsiTokenService $jitsiTokenService;

    public function __construct()
    {
        $this->jitsiTokenService = new JitsiTokenService();
    }

    /**
     * Initialize video session baru atau get existing
     * 
     * @param int $consultationId
     * @param int $userId User yang membuat session
     * 
     * @return VideoSession
     * @throws Exception
     */
    public function initializeSession(int $consultationId, int $userId): VideoSession
    {
        $consultation = Consultation::findOrFail($consultationId);

        // Verify user is doctor or patient
        if ($consultation->doctor_id !== $userId && $consultation->patient_id !== $userId) {
            throw new Exception('Unauthorized: Anda bukan bagian dari konsultasi ini');
        }

        // Check existing active session
        $existingSession = VideoSession::where('consultation_id', $consultationId)
            ->whereIn('status', [
                VideoSession::STATUS_PENDING,
                VideoSession::STATUS_RINGING,
                VideoSession::STATUS_ACTIVE
            ])
            ->first();

        if ($existingSession) {
            return $existingSession;
        }

        // Create new session
        $roomId = Str::uuid()->toString();
        
        $session = VideoSession::create([
            'consultation_id' => $consultationId,
            'doctor_id' => $consultation->doctor_id,
            'patient_id' => $consultation->patient_id,
            'room_id' => $roomId,
            'status' => VideoSession::STATUS_PENDING,
        ]);

        // Log event
        VideoSessionEvent::logEvent(
            $session->id,
            VideoSessionEvent::TYPE_INFO,
            'Session video diinisialisasi',
            ['initiated_by_id' => $userId]
        );

        return $session;
    }

    /**
     * Start video session (oleh doctor)
     * 
     * @param int $sessionId
     * @param int $doctorId
     * 
     * @return VideoSession
     * @throws Exception
     */
    public function startSession(int $sessionId, int $doctorId): VideoSession
    {
        $session = VideoSession::findOrFail($sessionId);

        // Verify doctor
        if ($session->doctor_id !== $doctorId) {
            throw new Exception('Unauthorized: Hanya dokter yang dapat memulai session');
        }

        if ($session->status !== VideoSession::STATUS_PENDING) {
            throw new Exception('Session tidak dalam status pending');
        }

        // Update status
        $session->update([
            'status' => VideoSession::STATUS_ACTIVE,
            'started_at' => now(),
        ]);

        // Log participant (doctor joined)
        VideoParticipantLog::createLog(
            $session->id,
            $doctorId,
            'joined',
            [
                'role' => 'doctor',
                'device_type' => 'desktop',
            ]
        );

        // Log event
        VideoSessionEvent::logEvent(
            $session->id,
            VideoSessionEvent::TYPE_INFO,
            'Session dimulai oleh dokter',
            ['doctor_id' => $doctorId]
        );

        return $session->fresh();
    }

    /**
     * End video session
     * 
     * @param int $sessionId
     * @param int $userId User yang end session
     * @param string|null $reason Alasan ending
     * @param string|null $callQuality Kualitas panggilan
     * 
     * @return VideoSession
     * @throws Exception
     */
    public function endSession(
        int $sessionId,
        int $userId,
        ?string $reason = null,
        ?string $callQuality = null
    ): VideoSession
    {
        $session = VideoSession::findOrFail($sessionId);

        // Verify user is part of session
        if ($session->doctor_id !== $userId && $session->patient_id !== $userId) {
            throw new Exception('Unauthorized: Anda bukan bagian dari session ini');
        }

        if ($session->isEnded()) {
            throw new Exception('Session sudah berakhir');
        }

        // Calculate duration
        $duration = null;
        if ($session->started_at) {
            $duration = $session->started_at->diffInSeconds(now());
        }

        // Update session
        $session->update([
            'status' => VideoSession::STATUS_ENDED,
            'ended_at' => now(),
            'duration_seconds' => $duration,
            'ended_reason' => $reason ?? VideoSession::REASON_USER_ENDED,
            'call_quality' => $callQuality,
        ]);

        // Log participant (left)
        VideoParticipantLog::createLog(
            $session->id,
            $userId,
            'left',
            [
                'reason' => $reason,
                'quality' => $callQuality,
            ]
        );

        // Log event
        VideoSessionEvent::logEvent(
            $session->id,
            VideoSessionEvent::TYPE_INFO,
            'Session berakhir',
            [
                'ended_by_id' => $userId,
                'reason' => $reason,
                'duration' => $duration,
            ]
        );

        return $session->fresh();
    }

    /**
     * Get Jitsi token untuk user
     * 
     * @param int $sessionId
     * @param int $userId
     * 
     * @return string JWT token
     * @throws Exception
     */
    public function getJitsiToken(int $sessionId, int $userId): string
    {
        $session = VideoSession::with('consultation', 'doctor', 'patient')
            ->findOrFail($sessionId);

        // Verify user is part of session
        if ($session->doctor_id !== $userId && $session->patient_id !== $userId) {
            throw new Exception('Unauthorized: Anda bukan bagian dari session ini');
        }

        // Get user data
        $user = \App\Models\User::findOrFail($userId);
        
        // Format room name
        $roomName = JitsiTokenService::formatRoomName($session->consultation_id);

        // Generate token berdasarkan role
        if ($session->doctor_id === $userId) {
            // Doctor = moderator
            $token = $this->jitsiTokenService->generateModeratorToken(
                $userId,
                $user->name,
                $user->email,
                $roomName
            );
        } else {
            // Patient = participant
            $token = $this->jitsiTokenService->generateParticipantToken(
                $userId,
                $user->name,
                $user->email,
                $roomName
            );
        }

        return $token;
    }

    /**
     * Get Jitsi configuration untuk frontend
     * 
     * @return array
     */
    public function getJitsiConfig(): array
    {
        return [
            'server_url' => config('jitsi.server_url'),
            'app_id' => config('jitsi.app_id'),
            'features' => config('jitsi.features'),
            'interface' => config('jitsi.interface'),
            'advanced' => config('jitsi.advanced'),
        ];
    }

    /**
     * Log event participant
     * 
     * @param int $sessionId
     * @param int $userId
     * @param string $eventType
     * @param array $data Additional data
     * 
     * @return VideoParticipantLog
     */
    public function logParticipantEvent(
        int $sessionId,
        int $userId,
        string $eventType,
        array $data = []
    ): VideoParticipantLog
    {
        return VideoParticipantLog::createLog($sessionId, $userId, $eventType, $data);
    }

    /**
     * Get session analytics
     * 
     * @param int $sessionId
     * 
     * @return array
     */
    public function getSessionAnalytics(int $sessionId): array
    {
        $session = VideoSession::with([
            'doctor:id,name,email',
            'patient:id,name,email',
            'participantLogs',
            'events'
        ])->findOrFail($sessionId);

        $doctorLogs = $session->participantLogs()->where('user_id', $session->doctor_id)->get();
        $patientLogs = $session->participantLogs()->where('user_id', $session->patient_id)->get();

        return [
            'session_id' => $session->id,
            'duration_seconds' => $session->duration_seconds,
            'duration_formatted' => $this->formatDuration($session->duration_seconds),
            'call_quality' => $session->call_quality,
            'status' => $session->status,
            'started_at' => $session->started_at,
            'ended_at' => $session->ended_at,
            'ended_reason' => $session->ended_reason,
            'doctor' => [
                'id' => $session->doctor->id,
                'name' => $session->doctor->name,
                'email' => $session->doctor->email,
                'joined_at' => $doctorLogs->where('event_type', 'joined')->first()?->timestamp,
                'left_at' => $doctorLogs->where('event_type', 'left')->last()?->timestamp,
                'final_quality' => $doctorLogs->last()?->connection_quality,
            ],
            'patient' => [
                'id' => $session->patient->id,
                'name' => $session->patient->name,
                'email' => $session->patient->email,
                'joined_at' => $patientLogs->where('event_type', 'joined')->first()?->timestamp,
                'left_at' => $patientLogs->where('event_type', 'left')->last()?->timestamp,
                'final_quality' => $patientLogs->last()?->connection_quality,
            ],
            'events_count' => $session->events()->count(),
            'warnings_count' => $session->events()->where('severity', 'high')->count(),
            'errors_count' => $session->events()->where('event_type', 'error')->count(),
            'participant_logs' => $session->participantLogs()->count(),
        ];
    }

    /**
     * Format duration dari seconds
     * 
     * @param int|null $seconds
     * 
     * @return string
     */
    private function formatDuration(?int $seconds): string
    {
        if ($seconds === null) {
            return '00:00:00';
        }

        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $secs = $seconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
    }

    /**
     * Check apakah session active
     * 
     * @param int $sessionId
     * 
     * @return bool
     */
    public function isSessionActive(int $sessionId): bool
    {
        $session = VideoSession::find($sessionId);
        return $session && $session->isActive();
    }

    /**
     * Get active sessions untuk user
     * 
     * @param int $userId
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActiveSessionsForUser(int $userId)
    {
        return VideoSession::where(function ($q) use ($userId) {
            $q->where('doctor_id', $userId)
              ->orWhere('patient_id', $userId);
        })
        ->whereIn('status', [
            VideoSession::STATUS_PENDING,
            VideoSession::STATUS_ACTIVE
        ])
        ->orderBy('created_at', 'desc')
        ->get();
    }
}
