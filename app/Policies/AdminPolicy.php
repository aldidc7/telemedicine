<?php

namespace App\Policies;

use App\Models\User;

class AdminPolicy
{
    /**
     * Determine if user dapat access admin panel
     */
    public function accessPanel(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine if user dapat view dashboard
     */
    public function viewDashboard(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine if user dapat manage pengguna
     */
    public function manageUsers(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine if user dapat view logs
     */
    public function viewLogs(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine if user dapat view statistik
     */
    public function viewStatistik(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine if user dapat deactivate/activate user
     */
    public function toggleUserStatus(User $user, User $targetUser): bool
    {
        // Admin bisa manage semua user kecuali admin lain
        if ($user->role !== 'admin') {
            return false;
        }

        // Admin tidak bisa deactivate dirinya sendiri
        if ($user->id === $targetUser->id) {
            return false;
        }

        return true;
    }

    /**
     * Determine if user dapat delete user
     */
    public function deleteUser(User $user, User $targetUser): bool
    {
        // Admin bisa delete semua user kecuali dirinya sendiri
        if ($user->role !== 'admin') {
            return false;
        }

        return $user->id !== $targetUser->id;
    }
}
