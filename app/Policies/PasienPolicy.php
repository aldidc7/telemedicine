<?php

namespace App\Policies;

use App\Models\Pasien;
use App\Models\User;

class PasienPolicy
{
    /**
     * Determine if user dapat view semua pasien
     */
    public function viewAny(User $user): bool
    {
        // Admin, Dokter, dan Pasien bisa view list
        return in_array($user->role, ['admin', 'dokter', 'pasien']);
    }

    /**
     * Determine if user dapat view specific pasien
     */
    public function view(User $user, Pasien $pasien): bool
    {
        // Admin bisa view siapa saja
        if ($user->role === 'admin') {
            return true;
        }

        // Pasien hanya bisa view dirinya sendiri
        if ($user->role === 'pasien') {
            return $user->id === $pasien->user_id;
        }

        // Dokter bisa view pasien yang punya konsultasi dengannya
        if ($user->role === 'dokter') {
            return $pasien->konsultasis()
                ->where('dokter_id', $user->dokter->id ?? null)
                ->exists();
        }

        return false;
    }

    /**
     * Determine if user dapat create pasien
     */
    public function create(User $user): bool
    {
        // Hanya admin yang bisa create pasien
        return $user->role === 'admin';
    }

    /**
     * Determine if user dapat update pasien
     */
    public function update(User $user, Pasien $pasien): bool
    {
        // Admin bisa update semua
        if ($user->role === 'admin') {
            return true;
        }

        // Pasien hanya bisa update dirinya sendiri
        if ($user->role === 'pasien') {
            return $user->id === $pasien->user_id;
        }

        return false;
    }

    /**
     * Determine if user dapat delete pasien
     */
    public function delete(User $user, Pasien $pasien): bool
    {
        // Hanya admin yang bisa delete
        return $user->role === 'admin';
    }

    /**
     * Determine if user dapat view rekam medis
     */
    public function viewRekamMedis(User $user, Pasien $pasien): bool
    {
        // Admin bisa view siapa saja
        if ($user->role === 'admin') {
            return true;
        }

        // Pasien hanya bisa view miliknya sendiri
        if ($user->role === 'pasien') {
            return $user->id === $pasien->user_id;
        }

        // Dokter bisa view pasien yang punya konsultasi dengannya
        if ($user->role === 'dokter') {
            return $pasien->konsultasis()
                ->where('dokter_id', $user->dokter->id ?? null)
                ->exists();
        }

        return false;
    }
}
