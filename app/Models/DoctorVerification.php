<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DoctorVerification extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'doctor_id',
        'kkmi_number',
        'verification_status',
        'verified_at',
        'verified_by',
        'kki_number',
        'sip_number',
        'specialization',
        'facility_name',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    // Verification status constants
    const STATUS_UNVERIFIED = 'unverified';
    const STATUS_PENDING = 'pending';
    const STATUS_VERIFIED = 'verified';
    const STATUS_REJECTED = 'rejected';

    public static $statuses = [
        self::STATUS_UNVERIFIED => 'Belum Diverifikasi',
        self::STATUS_PENDING => 'Menunggu Verifikasi',
        self::STATUS_VERIFIED => 'Terverifikasi',
        self::STATUS_REJECTED => 'Ditolak',
    ];

    /**
     * Relationships
     */
    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function verifiedByUser()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function credentials()
    {
        return $this->hasMany(DoctorCredential::class, 'doctor_id', 'doctor_id');
    }

    /**
     * Scopes
     */
    public function scopeVerified($query)
    {
        return $query->where('verification_status', self::STATUS_VERIFIED);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where('verification_status', self::STATUS_VERIFIED);
    }

    public function scopePending($query)
    {
        return $query->where('verification_status', self::STATUS_PENDING);
    }

    /**
     * Methods
     */
    public function verify($verifiedByUserId, $notes = null)
    {
        $this->update([
            'verification_status' => self::STATUS_VERIFIED,
            'verified_at' => now(),
            'verified_by' => $verifiedByUserId,
            'is_active' => true,
            'notes' => $notes,
        ]);

        return $this;
    }

    public function reject($notes = null)
    {
        $this->update([
            'verification_status' => self::STATUS_REJECTED,
            'is_active' => false,
            'notes' => $notes,
        ]);

        return $this;
    }

    public function requestVerification()
    {
        $this->update([
            'verification_status' => self::STATUS_PENDING,
        ]);

        return $this;
    }

    public function isVerified()
    {
        return $this->verification_status === self::STATUS_VERIFIED && $this->is_active;
    }

    public function getStatusLabel()
    {
        return self::$statuses[$this->verification_status] ?? $this->verification_status;
    }

    public function hasAllRequiredCredentials()
    {
        $requiredCredentials = [
            DoctorCredential::TYPE_KKI,
            DoctorCredential::TYPE_SIP,
        ];

        foreach ($requiredCredentials as $type) {
            if (!$this->hasValidCredential($type)) {
                return false;
            }
        }

        return true;
    }

    public function hasValidCredential($type)
    {
        return DoctorCredential::forDoctor($this->doctor_id)
            ->byType($type)
            ->verified()
            ->exists();
    }
}
