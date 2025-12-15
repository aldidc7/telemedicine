<?php

namespace App\Repositories;

use App\Models\PesanChat;

/**
 * PesanChat Repository
 * 
 * Menangani semua database queries untuk PesanChat model
 * dengan optimization dan eager loading
 */
class PesanChatRepository
{
    /**
     * Get messages for consultation with eager loading
     * FIXES N+1: Eager load pengirim (user)
     */
    public function getByKonsultasiId($konsultasiId, $perPage = 30)
    {
        return PesanChat::with('pengirim:id,name,email')
            ->where('konsultasi_id', $konsultasiId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get unread messages count
     */
    public function getUnreadCount($konsultasiId, $userId)
    {
        return PesanChat::where('konsultasi_id', $konsultasiId)
            ->where('dibaca', false)
            ->where('pengirim_id', '!=', $userId)
            ->count();
    }

    /**
     * Mark messages as read
     */
    public function markAsRead($konsultasiId, $userId)
    {
        return PesanChat::where('konsultasi_id', $konsultasiId)
            ->where('dibaca', false)
            ->where('pengirim_id', '!=', $userId)
            ->update(['dibaca' => true, 'dibaca_at' => now()]);
    }

    /**
     * Get latest message in consultation
     */
    public function getLatest($konsultasiId)
    {
        return PesanChat::where('konsultasi_id', $konsultasiId)
            ->with('pengirim:id,name')
            ->latest('created_at')
            ->first();
    }

    /**
     * Get message statistics
     */
    public function getStatistics($konsultasiId)
    {
        return PesanChat::where('konsultasi_id', $konsultasiId)
            ->selectRaw('
                COUNT(*) as total,
                COUNT(CASE WHEN dibaca = false THEN 1 END) as unread,
                COUNT(CASE WHEN tipe_pesan = "text" THEN 1 END) as text_count,
                COUNT(CASE WHEN tipe_pesan != "text" THEN 1 END) as file_count
            ')
            ->first();
    }

    /**
     * Delete message by ID
     */
    public function delete($id)
    {
        return PesanChat::destroy($id);
    }
}
