<?php

namespace App\Services;

use App\Models\PesanChat;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

/**
 * ============================================
 * SERVICE: PESAN CHAT SERVICE
 * ============================================
 * 
 * Business logic untuk pesan chat dalam konsultasi
 * - Create message
 * - Mark as read
 * - Delete message
 * - Fetch unread messages
 */
class PesanChatService
{
    /**
     * Create new message
     * 
     * @param User $user
     * @param array $data
     * @return PesanChat
     */
    public function createMessage(User $user, array $data): PesanChat
    {
        return PesanChat::create([
            'konsultasi_id' => $data['konsultasi_id'],
            'pengirim_id' => $user->id,
            'pesan' => $data['pesan'],
            'tipe_pesan' => $data['tipe_pesan'] ?? 'text',
            'url_file' => $data['url_file'] ?? null,
        ]);
    }

    /**
     * Mark message as read
     * 
     * @param PesanChat $pesan
     * @return PesanChat
     */
    public function markAsRead(PesanChat $pesan): PesanChat
    {
        if (!$pesan->dibaca_pada) {
            $pesan->update([
                'dibaca' => true,
                'dibaca_pada' => now(),
            ]);
        }
        
        return $pesan->fresh();
    }

    /**
     * Delete message
     * 
     * @param PesanChat $pesan
     * @return bool
     */
    public function deleteMessage(PesanChat $pesan): bool
    {
        return $pesan->delete();
    }

    /**
     * Get unread messages count for consultation
     * 
     * @param int $konsultasiId
     * @param int $userId
     * @return int
     */
    public function getUnreadCount(int $konsultasiId, int $userId): int
    {
        return PesanChat::where('konsultasi_id', $konsultasiId)
            ->where('dibaca', false)
            ->where('pengirim_id', '!=', $userId)
            ->count();
    }

    /**
     * Mark all messages in consultation as read
     * 
     * @param int $konsultasiId
     * @param int $userId
     * @return int
     */
    public function markAllAsRead(int $konsultasiId, int $userId): int
    {
        return PesanChat::where('konsultasi_id', $konsultasiId)
            ->where('pengirim_id', '!=', $userId)
            ->where('dibaca', false)
            ->update([
                'dibaca' => true,
                'dibaca_pada' => now(),
            ]);
    }
}
