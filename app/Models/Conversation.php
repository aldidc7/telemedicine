<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_1_id',
        'user_2_id',
        'last_message_at',
        'last_message_preview',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relasi ke user 1
     */
    public function user1()
    {
        return $this->belongsTo(User::class, 'user_1_id');
    }

    /**
     * Relasi ke user 2
     */
    public function user2()
    {
        return $this->belongsTo(User::class, 'user_2_id');
    }

    /**
     * Relasi ke messages
     */
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Get the other user dalam conversation
     */
    public function getOtherUser($userId)
    {
        return $this->user_1_id === $userId ? $this->user2 : $this->user1;
    }

    /**
     * Check if user is part of conversation
     */
    public function hasUser($userId): bool
    {
        return $this->user_1_id === $userId || $this->user_2_id === $userId;
    }

    /**
     * Mark all messages as read for a specific user
     */
    public function markAsRead($userId)
    {
        $this->messages()
            ->whereNull('read_at')
            ->where('sender_id', '!=', $userId)
            ->update(['read_at' => now()]);
    }

    /**
     * Get unread count for a user
     */
    public function getUnreadCount($userId)
    {
        return $this->messages()
            ->whereNull('read_at')
            ->where('sender_id', '!=', $userId)
            ->count();
    }
}
