<?php

namespace App\Policies;

use App\Models\Prescription;
use App\Models\User;

class PrescriptionPolicy
{
    /**
     * Determine if user can view the prescription
     */
    public function view(User $user, Prescription $prescription): bool
    {
        // Patient can view their own prescriptions
        if ($user->role === 'pasien') {
            return $prescription->patient_id === $user->id;
        }

        // Doctor can view prescriptions they created
        if ($user->role === 'dokter') {
            return $prescription->doctor_id === $user->id;
        }

        // Admin can view all
        if ($user->role === 'admin') {
            return true;
        }

        return false;
    }

    /**
     * Determine if user can update the prescription
     */
    public function update(User $user, Prescription $prescription): bool
    {
        // Only doctor who created it can update
        if ($user->role === 'dokter') {
            return $prescription->doctor_id === $user->id;
        }

        // Admin can update
        if ($user->role === 'admin') {
            return true;
        }

        return false;
    }

    /**
     * Determine if user can update prescription status
     */
    public function updateStatus(User $user, Prescription $prescription): bool
    {
        // Only doctor who created it can update status
        if ($user->role === 'dokter') {
            return $prescription->doctor_id === $user->id;
        }

        // Admin can update status
        if ($user->role === 'admin') {
            return true;
        }

        return false;
    }

    /**
     * Determine if user can delete the prescription
     */
    public function delete(User $user, Prescription $prescription): bool
    {
        // Only doctor who created it can delete
        if ($user->role === 'dokter') {
            return $prescription->doctor_id === $user->id;
        }

        // Admin can delete
        if ($user->role === 'admin') {
            return true;
        }

        return false;
    }
}
