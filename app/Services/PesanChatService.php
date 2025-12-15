<?php

namespace App\Services;

use App\Models\PesanChat;
use App\Models\Konsultasi;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Service untuk mengelola pesan chat dalam konsultasi
 */
class PesanChatService
{
    /**
     * Get list of messages for a consultation with pagination
     * 
     * @param int $konsultasiId
     * @param array $filters
     * @return mixed
     */
    public function getAllMessages(int $konsultasiId, array $filters = [])
    {
        $perPage = $filters['per_page'] ?? 20;
        $sort = $filters['sort'] ?? 'created_at';
        $order = $filters['order'] ?? 'asc';

        return PesanChat::where('konsultasi_id', $konsultasiId)
            ->with('pengguna')
            ->orderBy($sort, $order)
            ->paginate($perPage);
    }

    /**
     * Create new message
     * 
     * @param User $user
     * @param array $data
     * @return PesanChat
     */
    public function createMessage(User $user, array $data)
    {
        return DB::transaction(function () use ($user, $data) {
            $pesan = PesanChat::create([
                'konsultasi_id' => $data['konsultasi_id'],
                'pengguna_id' => $user->id,
                'pesan' => $data['pesan'],
                'is_read' => false,
            ]);

            return $pesan->load('pengguna');
        });
    }

    /**
     * Get message by ID
     * 
     * @param int $id
     * @return PesanChat|null
     */
    public function getMessageById(int $id)
    {
        return PesanChat::with('pengguna', 'konsultasi')->find($id);
    }

    /**
     * Delete message
     * 
     * @param PesanChat $pesan
     * @return bool
     */
    public function deleteMessage(PesanChat $pesan)
    {
        return $pesan->delete();
    }

    /**
     * Mark message as read
     * 
     * @param PesanChat $pesan
     * @return PesanChat
     */
    public function markAsRead(PesanChat $pesan)
    {
        return DB::transaction(function () use ($pesan) {
            $pesan->update(['is_read' => true]);
            return $pesan;
        });
    }

    /**
     * Get unread count for a consultation
     * 
     * @param int $konsultasiId
     * @return int
     */
    public function getUnreadCount(int $konsultasiId)
    {
        return PesanChat::where('konsultasi_id', $konsultasiId)
            ->where('is_read', false)
            ->count();
    }

    /**
     * Mark all messages as read for a consultation
     * 
     * @param int $konsultasiId
     * @param int $userId
     * @return bool
     */
    public function markAllAsRead(int $konsultasiId, int $userId)
    {
        return DB::transaction(function () use ($konsultasiId, $userId) {
            PesanChat::where('konsultasi_id', $konsultasiId)
                ->where('pengguna_id', '!=', $userId)
                ->where('is_read', false)
                ->update(['is_read' => true]);
            return true;
        });
    }
}
