<?php

namespace App\Policies;

use App\Models\User;
use App\Models\PesanChat;

class PesanChatPolicy
{
    /**
     * Determine if user can view message
     */
    public function view(User $user, PesanChat $pesanChat): bool
    {
        $konsultasi = $pesanChat->konsultasi;
        return $user->id === $konsultasi->doctor_id || 
               $user->id === $konsultasi->patient_id || 
               $user->role === 'admin';
    }

    /**
     * Determine if user can delete message
     */
    public function delete(User $user, PesanChat $pesanChat): bool
    {
        return $user->id === $pesanChat->pengirim_id || $user->role === 'admin';
    }
}
