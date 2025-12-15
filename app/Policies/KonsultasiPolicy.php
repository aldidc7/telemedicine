<?php

namespace App\Policies;

use App\Models\Konsultasi;
use App\Models\User;

class KonsultasiPolicy
{
    /**
     * Determine if user dapat view semua konsultasi
     */
    public function viewAny(User $user): bool
    {
        // Semua role bisa view list
        return true;
    }

    /**
     * Determine if user dapat view specific konsultasi
     */
    public function view(User $user, Konsultasi $konsultasi): bool
    {
        // Admin bisa view semua
        if ($user->role === 'admin') {
            return true;
        }

        // Pasien hanya bisa view konsultasinya sendiri
        if ($user->role === 'pasien') {
            return $user->id === $konsultasi->pasien->user_id;
        }

        // Dokter hanya bisa view konsultasinya sendiri
        if ($user->role === 'dokter') {
            return $user->id === $konsultasi->dokter->user_id;
        }

        return false;
    }

    /**
     * Determine if user dapat create konsultasi
     */
    public function create(User $user): bool
    {
        // Hanya pasien yang bisa buat konsultasi
        return $user->role === 'pasien';
    }

    /**
     * Determine if user dapat terima konsultasi
     */
    public function terima(User $user, Konsultasi $konsultasi): bool
    {
        // Hanya dokter yang terkait dan status menunggu
        if ($user->role === 'dokter') {
            return $user->id === $konsultasi->dokter->user_id 
                && $konsultasi->status === 'menunggu';
        }

        return false;
    }

    /**
     * Determine if user dapat tolak konsultasi
     */
    public function tolak(User $user, Konsultasi $konsultasi): bool
    {
        // Hanya dokter yang terkait dan status menunggu
        if ($user->role === 'dokter') {
            return $user->id === $konsultasi->dokter->user_id 
                && $konsultasi->status === 'menunggu';
        }

        return false;
    }

    /**
     * Determine if user dapat selesaikan konsultasi
     */
    public function selesaikan(User $user, Konsultasi $konsultasi): bool
    {
        // Hanya dokter yang terkait dan status diterima
        if ($user->role === 'dokter') {
            return $user->id === $konsultasi->dokter->user_id 
                && $konsultasi->status === 'diterima';
        }

        return false;
    }

    /**
     * Determine if user dapat delete konsultasi
     */
    public function delete(User $user, Konsultasi $konsultasi): bool
    {
        // Hanya admin yang bisa delete
        return $user->role === 'admin';
    }
}
