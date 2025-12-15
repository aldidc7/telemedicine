<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Config;

/**
 * Centralized Validation Helper
 * 
 * Extract semua validation logic yang duplik ke satu tempat
 * DRY Principle - Don't Repeat Yourself
 * 
 * Updated: Session 5 - Code Refactoring Phase
 */
class ValidationHelper
{
    /**
     * Validate appointment status transition
     * 
     * Cegah duplikasi di AppointmentService, AppointmentController, etc.
     * 
     * @param string $currentStatus Status sekarang
     * @param string $newStatus Status yang ingin diubah
     * @return bool True jika transition valid
     * @throws \InvalidArgumentException Jika status tidak valid
     */
    public static function validateAppointmentStatusTransition(string $currentStatus, string $newStatus): bool
    {
        $validStatuses = config('appointment.VALID_STATUSES');
        $transitions = config('appointment.STATUS_TRANSITIONS');
        
        // Validasi current status
        if (!in_array($currentStatus, $validStatuses)) {
            throw new \InvalidArgumentException("Invalid current status: {$currentStatus}");
        }
        
        // Validasi new status
        if (!in_array($newStatus, $validStatuses)) {
            throw new \InvalidArgumentException("Invalid new status: {$newStatus}");
        }
        
        // Check transition is allowed
        $allowedTransitions = $transitions[$currentStatus] ?? [];
        if (!in_array($newStatus, $allowedTransitions)) {
            throw new \InvalidArgumentException(
                "Invalid status transition from {$currentStatus} to {$newStatus}"
            );
        }
        
        return true;
    }
    
    /**
     * Validate consultation status transition
     * 
     * @param string $currentStatus Status sekarang
     * @param string $newStatus Status yang ingin diubah
     * @return bool True jika transition valid
     * @throws \InvalidArgumentException Jika status tidak valid
     */
    public static function validateConsultationStatusTransition(string $currentStatus, string $newStatus): bool
    {
        $validStatuses = config('appointment.CONSULTATION_STATUSES');
        $transitions = config('appointment.CONSULTATION_TRANSITIONS');
        
        if (!in_array($currentStatus, $validStatuses)) {
            throw new \InvalidArgumentException("Invalid consultation status: {$currentStatus}");
        }
        
        if (!in_array($newStatus, $validStatuses)) {
            throw new \InvalidArgumentException("Invalid consultation status: {$newStatus}");
        }
        
        $allowedTransitions = $transitions[$currentStatus] ?? [];
        if (!in_array($newStatus, $allowedTransitions)) {
            throw new \InvalidArgumentException(
                "Invalid consultation status transition from {$currentStatus} to {$newStatus}"
            );
        }
        
        return true;
    }
    
    /**
     * Validate prescription status transition
     * 
     * @param string $currentStatus Status sekarang
     * @param string $newStatus Status yang ingin diubah
     * @return bool True jika transition valid
     * @throws \InvalidArgumentException Jika status tidak valid
     */
    public static function validatePrescriptionStatusTransition(string $currentStatus, string $newStatus): bool
    {
        $validStatuses = config('appointment.PRESCRIPTION_STATUSES');
        $transitions = config('appointment.PRESCRIPTION_TRANSITIONS');
        
        if (!in_array($currentStatus, $validStatuses)) {
            throw new \InvalidArgumentException("Invalid prescription status: {$currentStatus}");
        }
        
        if (!in_array($newStatus, $validStatuses)) {
            throw new \InvalidArgumentException("Invalid prescription status: {$newStatus}");
        }
        
        $allowedTransitions = $transitions[$currentStatus] ?? [];
        if (!in_array($newStatus, $allowedTransitions)) {
            throw new \InvalidArgumentException(
                "Invalid prescription status transition from {$currentStatus} to {$newStatus}"
            );
        }
        
        return true;
    }
    
    /**
     * Validate rating status transition
     * 
     * @param string $currentStatus Status sekarang
     * @param string $newStatus Status yang ingin diubah
     * @return bool True jika transition valid
     * @throws \InvalidArgumentException Jika status tidak valid
     */
    public static function validateRatingStatusTransition(string $currentStatus, string $newStatus): bool
    {
        $validStatuses = config('appointment.RATING_STATUSES');
        $transitions = config('appointment.RATING_TRANSITIONS');
        
        if (!in_array($currentStatus, $validStatuses)) {
            throw new \InvalidArgumentException("Invalid rating status: {$currentStatus}");
        }
        
        if (!in_array($newStatus, $validStatuses)) {
            throw new \InvalidArgumentException("Invalid rating status: {$newStatus}");
        }
        
        $allowedTransitions = $transitions[$currentStatus] ?? [];
        if (!in_array($newStatus, $allowedTransitions)) {
            throw new \InvalidArgumentException(
                "Invalid rating status transition from {$currentStatus} to {$newStatus}"
            );
        }
        
        return true;
    }
    
    /**
     * Validate appointment status
     * 
     * @param string $status Status yang ingin divalidasi
     * @return bool True jika valid
     * @throws \InvalidArgumentException Jika status tidak valid
     */
    public static function validateAppointmentStatus(string $status): bool
    {
        $validStatuses = config('appointment.VALID_STATUSES');
        
        if (!in_array($status, $validStatuses)) {
            throw new \InvalidArgumentException(
                "Invalid appointment status: {$status}. Valid statuses: " . implode(', ', $validStatuses)
            );
        }
        
        return true;
    }
    
    /**
     * Validate consultation status
     * 
     * @param string $status Status yang ingin divalidasi
     * @return bool True jika valid
     * @throws \InvalidArgumentException Jika status tidak valid
     */
    public static function validateConsultationStatus(string $status): bool
    {
        $validStatuses = config('appointment.CONSULTATION_STATUSES');
        
        if (!in_array($status, $validStatuses)) {
            throw new \InvalidArgumentException(
                "Invalid consultation status: {$status}. Valid statuses: " . implode(', ', $validStatuses)
            );
        }
        
        return true;
    }
    
    /**
     * Get all valid appointment statuses
     * 
     * @return array
     */
    public static function getAppointmentStatuses(): array
    {
        return config('appointment.VALID_STATUSES');
    }
    
    /**
     * Get all valid consultation statuses
     * 
     * @return array
     */
    public static function getConsultationStatuses(): array
    {
        return config('appointment.CONSULTATION_STATUSES');
    }
    
    /**
     * Get all valid prescription statuses
     * 
     * @return array
     */
    public static function getPrescriptionStatuses(): array
    {
        return config('appointment.PRESCRIPTION_STATUSES');
    }
    
    /**
     * Get all valid rating statuses
     * 
     * @return array
     */
    public static function getRatingStatuses(): array
    {
        return config('appointment.RATING_STATUSES');
    }
    
    /**
     * Check if user role is valid
     * 
     * @param string $role Role yang ingin divalidasi
     * @return bool True jika valid
     */
    public static function validateUserRole(string $role): bool
    {
        $validRoles = array_values(config('application.ROLES'));
        return in_array($role, $validRoles);
    }
    
    /**
     * Validate appointment slot duration
     * 
     * @param int $minutes Duration dalam menit
     * @return bool True jika valid
     */
    public static function validateSlotDuration(int $minutes): bool
    {
        // Slot harus minimal 15 menit, maksimal 120 menit
        return $minutes >= 15 && $minutes <= 120;
    }
    
    /**
     * Get allowed appointment status transitions dari status tertentu
     * 
     * @param string $status Current status
     * @return array Array dari allowed transitions
     */
    public static function getAllowedAppointmentTransitions(string $status): array
    {
        $transitions = config('appointment.STATUS_TRANSITIONS');
        return $transitions[$status] ?? [];
    }
    
    /**
     * Get allowed consultation status transitions dari status tertentu
     * 
     * @param string $status Current status
     * @return array Array dari allowed transitions
     */
    public static function getAllowedConsultationTransitions(string $status): array
    {
        $transitions = config('appointment.CONSULTATION_TRANSITIONS');
        return $transitions[$status] ?? [];
    }
}
