<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * ============================================
 * MODEL APPOINTMENT REMINDER
 * ============================================
 * 
 * Track reminder status untuk appointments
 * Ensure users get notified sebelum konsultasi
 * 
 * Features:
 * - Multiple reminder channels (SMS, Email, Push)
 * - Configurable timing (24h, 2h sebelum appointment)
 * - Retry logic untuk failed reminders
 * - Audit trail untuk compliance
 * 
 * @property int $id
 * @property int $appointment_id - FK to appointments/konsultasi
 * @property int $user_id - Patient user ID
 * @property string $reminder_type - 'sms', 'email', 'push'
 * @property string $scheduled_for - When reminder should be sent
 * @property string $status - 'pending', 'sent', 'failed'
 * @property int $retry_count - Number of retry attempts
 * @property string $error_message - Last error if failed
 * @property timestamp $sent_at - When actually sent
 * @property timestamps
 */
class AppointmentReminder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'appointment_id',
        'user_id',
        'reminder_type',
        'scheduled_for',
        'status',
        'retry_count',
        'error_message',
        'sent_at',
    ];

    protected $casts = [
        'scheduled_for' => 'datetime',
        'sent_at' => 'datetime',
        'retry_count' => 'integer',
    ];

    // =====================
    // RELATIONSHIPS
    // =====================

    /**
     * Appointment yang di-remind
     */
    public function appointment()
    {
        return $this->belongsTo(Konsultasi::class, 'appointment_id');
    }

    /**
     * User yang di-remind
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // =====================
    // SCOPES
    // =====================

    /**
     * Scope: Get pending reminders
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: Get reminders ready to send
     */
    public function scopeReadyToSend($query)
    {
        return $query
            ->pending()
            ->where('scheduled_for', '<=', now())
            ->where('retry_count', '<', 3);
    }

    /**
     * Scope: Get failed reminders
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope: Get sent reminders
     */
    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    // =====================
    // METHODS
    // =====================

    /**
     * Mark reminder as sent
     */
    public function markAsSent()
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    /**
     * Mark as failed dengan error message
     */
    public function markAsFailed($errorMessage)
    {
        $this->increment('retry_count');

        $this->update([
            'error_message' => $errorMessage,
            'status' => $this->retry_count >= 3 ? 'failed' : 'pending',
        ]);
    }

    /**
     * Check if appointment sudah berlangsung
     */
    public function isAppointmentExpired(): bool
    {
        return $this->appointment?->appointment_time < now();
    }

    /**
     * Get readable reminder type
     */
    public function getReminderTypeLabel(): string
    {
        $labels = [
            'sms' => 'SMS Reminder',
            'email' => 'Email Reminder',
            'push' => 'Push Notification',
        ];

        return $labels[$this->reminder_type] ?? $this->reminder_type;
    }

    /**
     * Get readable status
     */
    public function getStatusLabel(): string
    {
        $labels = [
            'pending' => 'Pending',
            'sent' => 'Sent',
            'failed' => 'Failed',
        ];

        return $labels[$this->status] ?? $this->status;
    }
}
