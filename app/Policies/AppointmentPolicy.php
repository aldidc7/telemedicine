<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;

class AppointmentPolicy
{
    /**
     * Determine if user can view the appointment
     */
    public function view(User $user, Appointment $appointment): bool
    {
        // Patient can view their own appointments
        if ($user->role === 'pasien') {
            return $appointment->patient_id === $user->id;
        }

        // Doctor can view their own appointments
        if ($user->role === 'dokter') {
            return $appointment->doctor_id === $user->id;
        }

        // Admin can view all
        if ($user->role === 'admin') {
            return true;
        }

        return false;
    }

    /**
     * Determine if user can update the appointment
     */
    public function update(User $user, Appointment $appointment): bool
    {
        // Only doctor can update appointment status
        if ($user->role === 'dokter') {
            return $appointment->doctor_id === $user->id;
        }

        // Patient can only reschedule
        if ($user->role === 'pasien') {
            return $appointment->patient_id === $user->id;
        }

        // Admin can update
        if ($user->role === 'admin') {
            return true;
        }

        return false;
    }

    /**
     * Determine if user can cancel the appointment
     */
    public function cancel(User $user, Appointment $appointment): bool
    {
        // Patient can cancel their own appointments
        if ($user->role === 'pasien') {
            return $appointment->patient_id === $user->id;
        }

        // Doctor can cancel their own appointments
        if ($user->role === 'dokter') {
            return $appointment->doctor_id === $user->id;
        }

        // Admin can cancel
        if ($user->role === 'admin') {
            return true;
        }

        return false;
    }

    /**
     * Determine if user can reschedule the appointment
     */
    public function reschedule(User $user, Appointment $appointment): bool
    {
        // Only patient can reschedule
        if ($user->role === 'pasien') {
            return $appointment->patient_id === $user->id;
        }

        // Admin can reschedule
        if ($user->role === 'admin') {
            return true;
        }

        return false;
    }

    /**
     * Determine if user can delete the appointment
     */
    public function delete(User $user, Appointment $appointment): bool
    {
        // Only admin can delete appointments
        return $user->role === 'admin';
    }
}
