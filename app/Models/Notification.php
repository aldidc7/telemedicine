<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{
    use HasFactory, SoftDeletes;

    // Notification types
    const TYPE_APPOINTMENT = 'appointment';
    const TYPE_APPOINTMENT_REMINDER = 'appointment_reminder';
    const TYPE_APPOINTMENT_CANCELLED = 'appointment_cancelled';
    const TYPE_APPOINTMENT_RESCHEDULED = 'appointment_rescheduled';
    const TYPE_CONSULTATION_STARTED = 'consultation_started';
    const TYPE_CONSULTATION_ENDED = 'consultation_ended';
    const TYPE_MESSAGE = 'message';
    const TYPE_PAYMENT = 'payment';
    const TYPE_PAYMENT_SUCCESS = 'payment_success';
    const TYPE_PAYMENT_FAILED = 'payment_failed';
    const TYPE_PRESCRIPTION = 'prescription';
    const TYPE_RATING = 'rating';
    const TYPE_EMERGENCY = 'emergency';
    const TYPE_VERIFICATION = 'verification';
    const TYPE_CREDENTIAL_APPROVED = 'credential_approved';
    const TYPE_CREDENTIAL_REJECTED = 'credential_rejected';
    const TYPE_SYSTEM = 'system';

    // Channels
    const CHANNEL_IN_APP = 'in_app';
    const CHANNEL_EMAIL = 'email';
    const CHANNEL_SMS = 'sms';
    const CHANNEL_PUSH = 'push';

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'action_url',
        'notifiable_type',
        'notifiable_id',
        'read_at',
        'data',
        'channel',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'data' => 'json',
    ];

    /**
     * Relasi ke user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Polymorphic relasi ke model lain
     */
    public function notifiable()
    {
        return $this->morphTo();
    }

    /**
     * Check if notification is read
     */
    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    /**
     * Mark notification as read
     */
    public function markAsRead()
    {
        if (!$this->isRead()) {
            $this->update(['read_at' => now()]);
        }
    }

    /**
     * Mark notification as unread
     */
    public function markAsUnread()
    {
        $this->update(['read_at' => null]);
    }

    /**
     * Scope untuk unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope untuk recent notifications
     */
    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Scope untuk specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
