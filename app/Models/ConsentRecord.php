<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * ============================================
 * MODEL CONSENT RECORD - INFORMED CONSENT TRACKING
 * ============================================
 * 
 * Track telemedicine informed consent per regulatory requirements.
 * 
 * REGULATORY BASIS:
 * - Telemedicine Ethics (WHO/International Standards): 
 *   "Informed consent is an important issue... may be wise to obtain 
 *    informed consent in person first, as well as having backup options"
 * - Ryan Haight Act (US): Requires documented consent for telemedicine
 * - India Telemedicine Guidelines (2020): Requires informed consent
 * 
 * User MUST accept informed consent before:
 * - First consultation booking
 * - Accessing telemedicine features
 * 
 * @property int $id - Record ID
 * @property int $user_id - User who consented
 * @property string $consent_type - Type: 'telemedicine', 'data_processing', 'privacy_policy'
 * @property string $consent_text - Text they consented to
 * @property bool $accepted - Whether they accepted (true/false)
 * @property Carbon|null $accepted_at - When they accepted
 * @property string $ip_address - IP address when consenting
 * @property string $user_agent - Browser info
 * @property string $version - Policy version (for tracking updates)
 * @property Carbon|null $revoked_at - If they revoked consent
 * @property Carbon $created_at - Record creation time
 * @property Carbon $updated_at - Last update time
 * 
 * @author Aplikasi Telemedicine
 * @version 1.0
 */
class ConsentRecord extends Model
{
    use HasFactory;

    protected $table = 'consent_records';

    protected $fillable = [
        'user_id',
        'consent_type',
        'consent_text',
        'accepted',
        'accepted_at',
        'ip_address',
        'user_agent',
        'version',
        'revoked_at',
    ];

    protected $casts = [
        'accepted' => 'boolean',
        'accepted_at' => 'datetime',
        'revoked_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ============ RELATIONSHIPS ============

    /**
     * ConsentRecord belongs to User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ============ ACCESSORS ============

    /**
     * Check if consent is currently valid (accepted and not revoked)
     */
    public function getIsValidAttribute(): bool
    {
        return $this->accepted && is_null($this->revoked_at);
    }

    /**
     * Check if consent is expired (older than 1 year)
     */
    public function getIsExpiredAttribute(): bool
    {
        if (!$this->accepted_at) {
            return true;
        }
        return $this->accepted_at->addYear()->isPast();
    }

    // ============ SCOPES ============

    /**
     * Get active consents only
     */
    public function scopeActive($query)
    {
        return $query->where('accepted', true)
                     ->whereNull('revoked_at');
    }

    /**
     * Get telemedicine consents
     */
    public function scopeTelemedicine($query)
    {
        return $query->where('consent_type', 'telemedicine');
    }

    /**
     * Get by type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('consent_type', $type);
    }

    /**
     * Get recent consents
     */
    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    // ============ STATIC METHODS ============

    /**
     * Check if user has valid consent for specific type
     * 
     * @param int $userId
     * @param string $consentType
     * @return bool
     */
    public static function hasValidConsent(int $userId, string $consentType): bool
    {
        return self::where('user_id', $userId)
                   ->where('consent_type', $consentType)
                   ->active()
                   ->exists();
    }

    /**
     * Get or create consent record
     */
    public static function getOrCreateConsent(int $userId, string $consentType, string $consentText, string $ipAddress = null, string $userAgent = null)
    {
        return self::updateOrCreate(
            [
                'user_id' => $userId,
                'consent_type' => $consentType,
            ],
            [
                'consent_text' => $consentText,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'version' => '1.0',
            ]
        );
    }

    /**
     * Create consent acceptance record
     */
    public static function recordConsent(int $userId, string $consentType, string $consentText, string $ipAddress = null, string $userAgent = null)
    {
        $consent = self::getOrCreateConsent($userId, $consentType, $consentText, $ipAddress, $userAgent);
        $consent->update([
            'accepted' => true,
            'accepted_at' => now(),
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);
        return $consent;
    }

    /**
     * Revoke consent
     */
    public function revoke()
    {
        $this->update(['revoked_at' => now()]);
        return $this;
    }
};
