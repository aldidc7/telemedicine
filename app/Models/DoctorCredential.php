<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DoctorCredential extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'doctor_id',
        'credential_type',
        'credential_number',
        'issued_by',
        'issued_date',
        'expiry_date',
        'document_url',
        'status',
        'verified_at',
        'verified_by',
        'rejection_reason',
        'notes',
    ];

    protected $casts = [
        'issued_date' => 'date',
        'expiry_date' => 'date',
        'verified_at' => 'datetime',
    ];

    // Credential type constants
    const TYPE_KKI = 'kki'; // Kompetensi Klinisi Indonesia (doctor license)
    const TYPE_SIP = 'sip'; // Surat Ijin Praktik (practice permit)
    const TYPE_AIPKI = 'aipki'; // Indonesian Medical Doctor License
    const TYPE_SPESIALIS = 'spesialis'; // Specialist Certificate
    const TYPE_SUBSPESIALIS = 'subspesialis'; // Sub-specialist Certificate

    public static $types = [
        self::TYPE_KKI => 'KKI (Kompetensi Klinisi Indonesia)',
        self::TYPE_SIP => 'SIP (Surat Ijin Praktik)',
        self::TYPE_AIPKI => 'AIPKI (Indonesian Medical Doctor License)',
        self::TYPE_SPESIALIS => 'Spesialis',
        self::TYPE_SUBSPESIALIS => 'Sub-spesialis',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_VERIFIED = 'verified';
    const STATUS_REJECTED = 'rejected';
    const STATUS_EXPIRED = 'expired';
    const STATUS_UNDER_REVIEW = 'under_review';

    public static $statuses = [
        self::STATUS_PENDING => 'Menunggu Verifikasi',
        self::STATUS_UNDER_REVIEW => 'Sedang Diverifikasi',
        self::STATUS_VERIFIED => 'Terverifikasi',
        self::STATUS_REJECTED => 'Ditolak',
        self::STATUS_EXPIRED => 'Expired',
    ];

    /**
     * Relationships
     */
    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Scopes
     */
    public function scopeForDoctor($query, $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    public function scopeVerified($query)
    {
        return $query->where('status', self::STATUS_VERIFIED)
            ->where('expiry_date', '>', now());
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('credential_type', $type);
    }

    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now());
    }

    /**
     * Methods
     */
    public function isValid()
    {
        return $this->status === self::STATUS_VERIFIED
            && $this->expiry_date > now();
    }

    public function isExpired()
    {
        return $this->expiry_date <= now();
    }

    public function verify($verifiedByUserId = null)
    {
        $this->update([
            'status' => self::STATUS_VERIFIED,
            'verified_at' => now(),
            'verified_by' => $verifiedByUserId,
        ]);

        return $this;
    }

    public function reject($reason)
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'rejection_reason' => $reason,
        ]);

        return $this;
    }

    public function getTypeLabel()
    {
        return self::$types[$this->credential_type] ?? $this->credential_type;
    }

    public function getStatusLabel()
    {
        return self::$statuses[$this->status] ?? $this->status;
    }

    public function getDaysUntilExpiry()
    {
        return now()->diffInDays($this->expiry_date, false);
    }

    public function isExpiringSoon()
    {
        return $this->getDaysUntilExpiry() <= 30 && $this->getDaysUntilExpiry() > 0;
    }
}
