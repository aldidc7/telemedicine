<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'content',
        'attachment_path',
        'attachment_type',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relasi ke conversation
     */
    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Relasi ke sender (user yang mengirim)
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Check if message is read
     */
    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    /**
     * Mark message as read
     */
    public function markAsRead()
    {
        if ($this->read_at === null) {
            $this->update(['read_at' => now()]);
        }
    }

    /**
     * Get attachment URL if exists
     */
    public function getAttachmentUrl()
    {
        if ($this->attachment_path) {
            return asset('storage/' . $this->attachment_path);
        }
        return null;
    }
}
