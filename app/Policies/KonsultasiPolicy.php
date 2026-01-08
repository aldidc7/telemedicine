<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Konsultasi;

class KonsultasiPolicy
{
    /**
     * Determine if user can view consultation
     */
    public function view(User $user, Konsultasi $konsultasi): bool
    {
        return $user->id === $konsultasi->doctor_id || 
               $user->id === $konsultasi->patient_id || 
               $user->role === 'admin';
    }

    /**
     * Determine if user can update consultation
     */
    public function update(User $user, Konsultasi $konsultasi): bool
    {
        return $user->id === $konsultasi->doctor_id || $user->role === 'admin';
    }

    /**
     * Determine if user can delete consultation
     */
    public function delete(User $user, Konsultasi $konsultasi): bool
    {
        return $user->role === 'admin';
    }
}
