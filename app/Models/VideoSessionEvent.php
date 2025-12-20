<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * VideoSessionEvent Model
 * Tracks detailed events during video session for analytics and debugging
 * Immutable record
 */
class VideoSessionEvent extends Model
{
    protected $table = 'video_session_events';

    public $timestamps = false;

    protected $fillable = [
        'video_session_id',
        'event_type',
        'severity',
        'description',
        'details',
        'timestamp',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'details' => 'json',
    ];

    /**
     * Event Type Constants
     */
    public const TYPE_INFO = 'info';
    public const TYPE_WARNING = 'warning';
    public const TYPE_ERROR = 'error';
    public const TYPE_DEBUG = 'debug';

    /**
     * Severity Constants
     */
    public const SEVERITY_LOW = 'low';
    public const SEVERITY_MEDIUM = 'medium';
    public const SEVERITY_HIGH = 'high';
    public const SEVERITY_CRITICAL = 'critical';

    /**
     * Relationships
     */
    public function videoSession(): BelongsTo
    {
        return $this->belongsTo(VideoSession::class);
    }

    /**
     * Scopes
     */
    public function scopeByType($query, $type)
    {
        return $query->where('event_type', $type);
    }

    public function scopeBySeverity($query, $severity)
    {
        return $query->where('severity', $severity);
    }

    public function scopeErrors($query)
    {
        return $query->where('event_type', self::TYPE_ERROR);
    }

    /**
     * Methods
     */
    public static function logEvent($sessionId, $type, $description, $details = [], $severity = self::SEVERITY_LOW): self
    {
        return self::create([
            'video_session_id' => $sessionId,
            'event_type' => $type,
            'severity' => $severity,
            'description' => $description,
            'details' => $details,
            'timestamp' => now(),
        ]);
    }

    public function getTypeLabel(): string
    {
        return match($this->event_type) {
            self::TYPE_INFO => 'Info',
            self::TYPE_WARNING => 'Peringatan',
            self::TYPE_ERROR => 'Error',
            self::TYPE_DEBUG => 'Debug',
            default => $this->event_type,
        };
    }

    public function getSeverityLabel(): string
    {
        return match($this->severity) {
            self::SEVERITY_LOW => 'Rendah',
            self::SEVERITY_MEDIUM => 'Sedang',
            self::SEVERITY_HIGH => 'Tinggi',
            self::SEVERITY_CRITICAL => 'Kritis',
            default => $this->severity,
        };
    }

    public function getSeverityColor(): string
    {
        return match($this->severity) {
            self::SEVERITY_LOW => 'blue',
            self::SEVERITY_MEDIUM => 'yellow',
            self::SEVERITY_HIGH => 'orange',
            self::SEVERITY_CRITICAL => 'red',
            default => 'gray',
        };
    }
}
