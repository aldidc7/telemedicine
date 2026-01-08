<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Dokter;

class DokterPolicy
{
    /**
     * Determine if user can view doctor
     */
    public function view(User $user, Dokter $dokter): bool
    {
        return $user->role === 'admin' || $user->role === 'pasien' || $user->id === $dokter->user_id;
    }

    /**
     * Determine if user can update doctor
     */
    public function update(User $user, Dokter $dokter): bool
    {
        return $user->id === $dokter->user_id || $user->role === 'admin';
    }

    /**
     * Determine if user can delete doctor
     */
    public function delete(User $user, Dokter $dokter): bool
    {
        return $user->role === 'admin';
    }
}
