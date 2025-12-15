<?php

namespace App\Policies;

use App\Models\Dokter;
use App\Models\User;

class DokterPolicy
{
    /**
     * Determine if user dapat view semua dokter
     */
    public function viewAny(User $user): bool
    {
        // Semua role bisa view list dokter
        return true;
    }

    /**
     * Determine if user dapat view specific dokter
     */
    public function view(User $user, Dokter $dokter): bool
    {
        // Admin dan Pasien bisa view semua dokter
        if (in_array($user->role, ['admin', 'pasien'])) {
            return true;
        }

        // Dokter hanya bisa view dirinya sendiri
        if ($user->role === 'dokter') {
            return $user->id === $dokter->user_id;
        }

        return false;
    }

    /**
     * Determine if user dapat create dokter
     */
    public function create(User $user): bool
    {
        // Hanya admin yang bisa create dokter
        return $user->role === 'admin';
    }

    /**
     * Determine if user dapat update dokter
     */
    public function update(User $user, Dokter $dokter): bool
    {
        // Admin bisa update semua
        if ($user->role === 'admin') {
            return true;
        }

        // Dokter hanya bisa update dirinya sendiri
        if ($user->role === 'dokter') {
            return $user->id === $dokter->user_id;
        }

        return false;
    }

    /**
     * Determine if user dapat update ketersediaan
     */
    public function updateKetersediaan(User $user, Dokter $dokter): bool
    {
        // Admin dan dokter sendiri bisa update ketersediaan
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'dokter') {
            return $user->id === $dokter->user_id;
        }

        return false;
    }

    /**
     * Determine if user dapat delete dokter
     */
    public function delete(User $user, Dokter $dokter): bool
    {
        // Hanya admin yang bisa delete
        return $user->role === 'admin';
    }
}
