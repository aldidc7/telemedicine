<?php

namespace App\Policies;

use App\Models\PesanChat;
use App\Models\User;

class PesanChatPolicy
{
    /**
     * Determine if user dapat view pesan
     */
    public function viewAny(User $user): bool
    {
        // Semua role yang authenticated bisa view
        return true;
    }

    /**
     * Determine if user dapat view specific pesan
     */
    public function view(User $user, PesanChat $pesan): bool
    {
        // Admin bisa view semua
        if ($user->role === 'admin') {
            return true;
        }

        // User hanya bisa view pesan konsultasi yang terlibat
        $konsultasi = $pesan->konsultasi;
        
        if ($user->role === 'pasien') {
            return $user->id === $konsultasi->pasien->user_id;
        }

        if ($user->role === 'dokter') {
            return $user->id === $konsultasi->dokter->user_id;
        }

        return false;
    }

    /**
     * Determine if user dapat create pesan
     */
    public function create(User $user): bool
    {
        // Pasien dan dokter bisa buat pesan
        return in_array($user->role, ['pasien', 'dokter']);
    }

    /**
     * Determine if user dapat update/mark dibaca pesan
     */
    public function markDibaca(User $user, PesanChat $pesan): bool
    {
        // Admin bisa mark semua
        if ($user->role === 'admin') {
            return true;
        }

        // User hanya bisa mark pesan yang bukan dikirimnya
        $konsultasi = $pesan->konsultasi;
        
        if ($user->role === 'pasien') {
            return $user->id === $konsultasi->pasien->user_id 
                && $pesan->pengirim_id !== $user->id;
        }

        if ($user->role === 'dokter') {
            return $user->id === $konsultasi->dokter->user_id 
                && $pesan->pengirim_id !== $user->id;
        }

        return false;
    }

    /**
     * Determine if user dapat delete pesan
     */
    public function delete(User $user, PesanChat $pesan): bool
    {
        // Admin bisa delete semua
        if ($user->role === 'admin') {
            return true;
        }

        // User hanya bisa delete pesan miliknya sendiri
        return $user->id === $pesan->pengirim_id;
    }
}
