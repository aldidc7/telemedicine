<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * SMS Log Model
 * Tracks SMS delivery status and history
 */
class SMSLog extends Model
{
    use HasFactory;

    protected $table = 'sms_logs';

    protected $fillable = [
        'user_id',
        'phone_number',
        'message',
        'template_type',
        'status',
        'external_id',
        'error_message',
        'attempt_count',
        'last_attempt_at',
        'delivered_at',
    ];

    protected $casts = [
        'last_attempt_at' => 'datetime',
        'delivered_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user associated with this SMS log
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: Get pending SMS logs
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: Get failed SMS logs
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope: Get delivered SMS logs
     */
    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    /**
     * Mark as delivered
     */
    public function markDelivered()
    {
        $this->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);
    }

    /**
     * Mark as failed
     */
    public function markFailed($errorMessage = null)
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
            'last_attempt_at' => now(),
        ]);
    }

    /**
     * Increment attempt count
     */
    public function incrementAttempt()
    {
        $this->update([
            'attempt_count' => $this->attempt_count + 1,
            'last_attempt_at' => now(),
        ]);
    }
}
