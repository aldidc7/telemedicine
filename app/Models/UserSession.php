<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserSession extends Model
{
    use HasFactory;

    protected $table = 'user_sessions';
    protected $fillable = [
        'user_id',
        'token',
        'ip_address',
        'user_agent',
        'device_name',
        'last_activity_at',
        'expires_at',
        'is_active'
    ];

    protected $casts = [
        'last_activity_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relationship dengan User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope untuk active sessions
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                     ->where('expires_at', '>', now());
    }

    /**
     * Update last activity time
     */
    public function updateActivity()
    {
        $this->update([
            'last_activity_at' => now()
        ]);
    }

    /**
     * Deactivate session (logout)
     */
    public function deactivate()
    {
        $this->update([
            'is_active' => false,
            'expires_at' => now()
        ]);
    }

    /**
     * Get browser/device info dari user agent
     */
    public function getBrowserInfoAttribute()
    {
        // Simple parsing, bisa diperluas dengan jensseeger/php-useragent
        if (strpos($this->user_agent, 'Chrome') !== false) {
            return 'Chrome';
        } elseif (strpos($this->user_agent, 'Firefox') !== false) {
            return 'Firefox';
        } elseif (strpos($this->user_agent, 'Safari') !== false) {
            return 'Safari';
        } elseif (strpos($this->user_agent, 'Edge') !== false) {
            return 'Edge';
        } else {
            return 'Unknown';
        }
    }

    /**
     * Get OS info dari user agent
     */
    public function getOsInfoAttribute()
    {
        if (strpos($this->user_agent, 'Windows') !== false) {
            return 'Windows';
        } elseif (strpos($this->user_agent, 'Mac') !== false) {
            return 'macOS';
        } elseif (strpos($this->user_agent, 'Linux') !== false) {
            return 'Linux';
        } elseif (strpos($this->user_agent, 'iPhone') !== false) {
            return 'iOS';
        } elseif (strpos($this->user_agent, 'Android') !== false) {
            return 'Android';
        } else {
            return 'Unknown';
        }
    }
}
