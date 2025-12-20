<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * VideoParticipantLog Model
 * Tracks participant join/leave events and connection quality
 * Immutable record for audit trail
 */
class VideoParticipantLog extends Model
{
    protected $table = 'video_participant_logs';

    public $timestamps = false;

    protected $fillable = [
        'video_session_id',
        'user_id',
        'event_type',
        'timestamp',
        'ip_address',
        'device_type',
        'browser_info',
        'connection_quality',
        'audio_enabled',
        'video_enabled',
        'screen_shared',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'audio_enabled' => 'boolean',
        'video_enabled' => 'boolean',
        'screen_shared' => 'boolean',
    ];

    /**
     * Event Type Constants
     */
    public const EVENT_JOINED = 'joined';
    public const EVENT_LEFT = 'left';
    public const EVENT_AUDIO_ENABLED = 'audio_enabled';
    public const EVENT_AUDIO_DISABLED = 'audio_disabled';
    public const EVENT_VIDEO_ENABLED = 'video_enabled';
    public const EVENT_VIDEO_DISABLED = 'video_disabled';
    public const EVENT_SCREEN_SHARED = 'screen_shared';
    public const EVENT_SCREEN_UNSHARED = 'screen_unshared';
    public const EVENT_NETWORK_DEGRADED = 'network_degraded';
    public const EVENT_NETWORK_RECOVERED = 'network_recovered';

    /**
     * Device Type Constants
     */
    public const DEVICE_DESKTOP = 'desktop';
    public const DEVICE_LAPTOP = 'laptop';
    public const DEVICE_TABLET = 'tablet';
    public const DEVICE_MOBILE = 'mobile';

    /**
     * Relationships
     */
    public function videoSession(): BelongsTo
    {
        return $this->belongsTo(VideoSession::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scopes
     */
    public function scopeByEvent($query, $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeBySession($query, $sessionId)
    {
        return $query->where('video_session_id', $sessionId);
    }

    /**
     * Methods
     */
    public static function createLog($sessionId, $userId, $eventType, $data = []): self
    {
        return self::create([
            'video_session_id' => $sessionId,
            'user_id' => $userId,
            'event_type' => $eventType,
            'timestamp' => now(),
            'ip_address' => $data['ip_address'] ?? null,
            'device_type' => $data['device_type'] ?? null,
            'browser_info' => $data['browser_info'] ?? null,
            'connection_quality' => $data['connection_quality'] ?? null,
            'audio_enabled' => $data['audio_enabled'] ?? false,
            'video_enabled' => $data['video_enabled'] ?? false,
            'screen_shared' => $data['screen_shared'] ?? false,
        ]);
    }

    public function getEventLabel(): string
    {
        return match($this->event_type) {
            self::EVENT_JOINED => 'Bergabung',
            self::EVENT_LEFT => 'Keluar',
            self::EVENT_AUDIO_ENABLED => 'Audio Diaktifkan',
            self::EVENT_AUDIO_DISABLED => 'Audio Dinonaktifkan',
            self::EVENT_VIDEO_ENABLED => 'Video Diaktifkan',
            self::EVENT_VIDEO_DISABLED => 'Video Dinonaktifkan',
            self::EVENT_SCREEN_SHARED => 'Screen Dibagikan',
            self::EVENT_SCREEN_UNSHARED => 'Screen Berhenti Dibagikan',
            self::EVENT_NETWORK_DEGRADED => 'Jaringan Menurun',
            self::EVENT_NETWORK_RECOVERED => 'Jaringan Pulih',
            default => $this->event_type,
        };
    }

    public function getDeviceLabel(): ?string
    {
        if (!$this->device_type) return null;

        return match($this->device_type) {
            self::DEVICE_DESKTOP => 'Desktop',
            self::DEVICE_LAPTOP => 'Laptop',
            self::DEVICE_TABLET => 'Tablet',
            self::DEVICE_MOBILE => 'Mobile',
            default => $this->device_type,
        };
    }
}
