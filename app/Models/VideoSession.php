<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * VideoSession Model
 * Represents a video consultation session between doctor and patient
 */
class VideoSession extends Model
{
    use SoftDeletes;

    protected $table = 'video_sessions';

    protected $fillable = [
        'consultation_id',
        'doctor_id',
        'patient_id',
        'room_id',
        'status',
        'started_at',
        'ended_at',
        'duration_seconds',
        'recording_url',
        'is_recorded',
        'recording_size_bytes',
        'screen_sharing_enabled',
        'call_quality',
        'ended_reason',
        'notes',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'is_recorded' => 'boolean',
        'screen_sharing_enabled' => 'boolean',
        'recording_size_bytes' => 'integer',
        'duration_seconds' => 'integer',
    ];

    /**
     * Status Constants
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_RINGING = 'ringing';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_PAUSED = 'paused';
    public const STATUS_ENDED = 'ended';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_MISSED = 'missed';

    /**
     * End Reason Constants
     */
    public const REASON_NORMAL = 'normal';
    public const REASON_TIMEOUT = 'timeout';
    public const REASON_PATIENT_DISCONNECT = 'patient_disconnect';
    public const REASON_DOCTOR_DISCONNECT = 'doctor_disconnect';
    public const REASON_NETWORK_ERROR = 'network_error';
    public const REASON_USER_ENDED = 'user_ended';

    /**
     * Call Quality Constants
     */
    public const QUALITY_EXCELLENT = 'excellent';
    public const QUALITY_GOOD = 'good';
    public const QUALITY_FAIR = 'fair';
    public const QUALITY_POOR = 'poor';

    /**
     * Relationships
     */
    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function participantLogs(): HasMany
    {
        return $this->hasMany(VideoParticipantLog::class, 'video_session_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(VideoSessionEvent::class, 'video_session_id');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeEnded($query)
    {
        return $query->where('status', self::STATUS_ENDED);
    }

    public function scopeByDoctor($query, $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    public function scopeByPatient($query, $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    public function scopeRecorded($query)
    {
        return $query->where('is_recorded', true);
    }

    /**
     * Methods
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isEnded(): bool
    {
        return $this->status === self::STATUS_ENDED;
    }

    public function start(): void
    {
        $this->update([
            'status' => self::STATUS_ACTIVE,
            'started_at' => now(),
        ]);
    }

    public function pause(): void
    {
        $this->update(['status' => self::STATUS_PAUSED]);
    }

    public function resume(): void
    {
        $this->update(['status' => self::STATUS_ACTIVE]);
    }

    public function end($reason = self::REASON_NORMAL): void
    {
        $duration = $this->started_at ? now()->diffInSeconds($this->started_at) : 0;

        $this->update([
            'status' => self::STATUS_ENDED,
            'ended_at' => now(),
            'duration_seconds' => $duration,
            'ended_reason' => $reason,
        ]);
    }

    public function markRecorded($recordingUrl, $sizeBytes = null): void
    {
        $this->update([
            'is_recorded' => true,
            'recording_url' => $recordingUrl,
            'recording_size_bytes' => $sizeBytes,
        ]);
    }

    public function enableScreenShare(): void
    {
        $this->update(['screen_sharing_enabled' => true]);
    }

    public function disableScreenShare(): void
    {
        $this->update(['screen_sharing_enabled' => false]);
    }

    public function setCallQuality($quality): void
    {
        $this->update(['call_quality' => $quality]);
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'Menunggu',
            self::STATUS_RINGING => 'Memanggil',
            self::STATUS_ACTIVE => 'Berlangsung',
            self::STATUS_PAUSED => 'Dijeda',
            self::STATUS_ENDED => 'Selesai',
            self::STATUS_CANCELLED => 'Dibatalkan',
            self::STATUS_MISSED => 'Terlewat',
            default => $this->status,
        };
    }

    public function getEndReasonLabel(): string
    {
        return match($this->ended_reason) {
            self::REASON_NORMAL => 'Selesai Normal',
            self::REASON_TIMEOUT => 'Timeout',
            self::REASON_PATIENT_DISCONNECT => 'Pasien Disconnect',
            self::REASON_DOCTOR_DISCONNECT => 'Dokter Disconnect',
            self::REASON_NETWORK_ERROR => 'Error Jaringan',
            self::REASON_USER_ENDED => 'Pengguna Mengakhiri',
            default => $this->ended_reason,
        };
    }

    public function getQualityLabel(): ?string
    {
        if (!$this->call_quality) return null;

        return match($this->call_quality) {
            self::QUALITY_EXCELLENT => 'Sempurna',
            self::QUALITY_GOOD => 'Baik',
            self::QUALITY_FAIR => 'Cukup',
            self::QUALITY_POOR => 'Buruk',
            default => $this->call_quality,
        };
    }

    public function getDurationFormatted(): string
    {
        if (!$this->duration_seconds) {
            return '0 menit';
        }

        $minutes = intdiv($this->duration_seconds, 60);
        $seconds = $this->duration_seconds % 60;

        if ($minutes === 0) {
            return "{$seconds} detik";
        }

        return "{$minutes} menit {$seconds} detik";
    }

    public function getRecordingSizeFormatted(): ?string
    {
        if (!$this->recording_size_bytes) {
            return null;
        }

        $bytes = $this->recording_size_bytes;
        $units = ['B', 'KB', 'MB', 'GB'];

        $index = 0;
        while ($bytes >= 1024 && $index < count($units) - 1) {
            $bytes /= 1024;
            $index++;
        }

        return round($bytes, 2) . ' ' . $units[$index];
    }
}
