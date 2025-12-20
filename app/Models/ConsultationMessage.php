<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * ============================================
 * MODEL CONSULTATION MESSAGE
 * ============================================
 * 
 * Messages yang dikirim selama/setelah konsultasi
 * Part dari chat history untuk konsultasi
 * 
 * @property int $id
 * @property int $consultation_id - FK to konsultasi
 * @property int $sender_id - User yang mengirim
 * @property text $message - Isi pesan
 * @property string|null $file_url - Optional: attachment
 * @property string|null $file_type - Type: image, document, prescription
 * @property boolean $is_read - Sudah dibaca?
 * @property timestamp $read_at - Waktu dibaca
 * @property timestamps
 */
class ConsultationMessage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'consultation_id',
        'sender_id',
        'message',
        'file_url',
        'file_type',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // =====================
    // RELATIONSHIPS
    // =====================

    public function consultation()
    {
        return $this->belongsTo(Konsultasi::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    // =====================
    // SCOPES
    // =====================

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeForConsultation($query, $consultationId)
    {
        return $query->where('consultation_id', $consultationId);
    }

    // =====================
    // METHODS
    // =====================

    public function markAsRead()
    {
        if (!$this->is_read) {
            $this->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }
    }

    public function isSenderDoctor(): bool
    {
        return $this->sender->role === 'doctor';
    }

    public function isSenderPatient(): bool
    {
        return $this->sender->role === 'patient';
    }

    public function getFormattedMessage(): string
    {
        return nl2br(e($this->message));
    }
}
