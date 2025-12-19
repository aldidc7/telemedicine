<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ApiKey extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'key',
        'secret',
        'type',
        'user_id',
        'description',
        'permissions',
        'rate_limit',
        'last_used_at',
        'is_active',
        'expires_at',
    ];

    protected $casts = [
        'permissions' => 'json',
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
        'last_used_at' => 'datetime',
    ];

    protected $hidden = ['secret'];

    /**
     * Generate new API key
     */
    public static function generateNew(string $name, string $type = 'general', ?int $userId = null): self
    {
        return self::create([
            'name' => $name,
            'key' => 'sk_' . Str::random(32),
            'secret' => Str::random(64),
            'type' => $type,
            'user_id' => $userId,
        ]);
    }

    /**
     * Validate API key
     */
    public static function validate(string $key, string $secret): ?self
    {
        $apiKey = self::where('key', $key)
            ->where('is_active', true)
            ->first();

        if (!$apiKey) {
            return null;
        }

        // Check expiration
        if ($apiKey->expires_at && $apiKey->expires_at->isPast()) {
            return null;
        }

        // Verify secret if provided
        if ($apiKey->secret && !hash_equals($apiKey->secret, $secret)) {
            return null;
        }

        return $apiKey;
    }

    /**
     * Update last used timestamp
     */
    public function recordUsage(): void
    {
        $this->update(['last_used_at' => now()]);
    }

    /**
     * Check if key has permission
     */
    public function hasPermission(string $permission): bool
    {
        if (empty($this->permissions)) {
            return true; // Allow all if no permissions set
        }

        return in_array($permission, $this->permissions);
    }

    /**
     * Relationship to user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
