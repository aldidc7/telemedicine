<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Pasien;

class PasienPolicy
{
    /**
     * Determine if user can view patient
     */
    public function view(User $user, Pasien $pasien): bool
    {
        return $user->id === $pasien->user_id || $user->role === 'admin' || $user->role === 'dokter';
    }

    /**
     * Determine if user can update patient
     */
    public function update(User $user, Pasien $pasien): bool
    {
        return $user->id === $pasien->user_id || $user->role === 'admin';
    }

    /**
     * Determine if user can delete patient
     */
    public function delete(User $user, Pasien $pasien): bool
    {
        return $user->role === 'admin';
    }
}
