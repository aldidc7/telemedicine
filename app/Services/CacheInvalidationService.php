<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Cache Invalidation Service
 * 
 * Manages strategic cache invalidation
 * Prevents stale data while maximizing cache efficiency
 */
class CacheInvalidationService
{
    /**
     * Invalidate cache when appointment is booked
     */
    public function onAppointmentBooked(int $doctorId, int $patientId, string $date): void
    {
        // Invalidate doctor's available slots for this date
        Cache::tags('appointments')->forget("slots:{$doctorId}:{$date}");

        // Invalidate doctor's overall availability
        Cache::tags('doctors')->flush();

        // Invalidate doctor's statistics (appointment count changed)
        Cache::tags('dashboard')->forget("doctor_statistics:{$doctorId}");

        // Invalidate patient's appointment list
        Cache::tags('patient_data')->forget("patient_appointments:{$patientId}");

        Log::info('Cache invalidated on appointment booked', [
            'doctor_id' => $doctorId,
            'patient_id' => $patientId,
            'date' => $date
        ]);
    }

    /**
     * Invalidate cache when appointment is confirmed
     */
    public function onAppointmentConfirmed(int $appointmentId, int $patientId, int $doctorId): void
    {
        // Invalidate patient's appointment list (status changed)
        Cache::tags('patient_data')->forget("patient_appointments:{$patientId}");

        // Invalidate doctor's statistics if needed
        Cache::tags('dashboard')->forget("doctor_statistics:{$doctorId}");

        Log::info('Cache invalidated on appointment confirmed', [
            'appointment_id' => $appointmentId,
            'patient_id' => $patientId,
            'doctor_id' => $doctorId
        ]);
    }

    /**
     * Invalidate cache when appointment is completed
     */
    public function onAppointmentCompleted(int $appointmentId, int $patientId, int $doctorId): void
    {
        // Invalidate patient's appointment list
        Cache::tags('patient_data')->forget("patient_appointments:{$patientId}");

        // Invalidate doctor's statistics (completion rate changed)
        Cache::tags('dashboard')->forget("doctor_statistics:{$doctorId}");

        // Invalidate doctor's rating cache (may affect average)
        Cache::tags('ratings')->forget("rating_average:doctor:{$doctorId}");

        Log::info('Cache invalidated on appointment completed', [
            'appointment_id' => $appointmentId,
            'patient_id' => $patientId,
            'doctor_id' => $doctorId
        ]);
    }

    /**
     * Invalidate cache when appointment is cancelled
     */
    public function onAppointmentCancelled(int $appointmentId, int $patientId, int $doctorId, string $date): void
    {
        // Invalidate doctor's slots (appointment was cancelled, slot becomes available)
        Cache::tags('appointments')->forget("slots:{$doctorId}:{$date}");

        // Invalidate doctor's availability
        Cache::tags('doctors')->flush();

        // Invalidate patient's appointment list
        Cache::tags('patient_data')->forget("patient_appointments:{$patientId}");

        // Invalidate doctor's statistics
        Cache::tags('dashboard')->forget("doctor_statistics:{$doctorId}");

        Log::info('Cache invalidated on appointment cancelled', [
            'appointment_id' => $appointmentId,
            'patient_id' => $patientId,
            'doctor_id' => $doctorId,
            'date' => $date
        ]);
    }

    /**
     * Invalidate cache when consultation starts
     */
    public function onConsultationStarted(int $consultationId, int $patientId, int $doctorId): void
    {
        // Invalidate doctor's consultation list
        Cache::tags('consultations')->forget("consultation_list:doctor:{$doctorId}");

        // Invalidate patient's consultation list
        Cache::tags('consultations')->forget("consultation_list:patient:{$patientId}");

        Log::info('Cache invalidated on consultation started', [
            'consultation_id' => $consultationId,
            'patient_id' => $patientId,
            'doctor_id' => $doctorId
        ]);
    }

    /**
     * Invalidate cache when consultation ends
     */
    public function onConsultationEnded(int $consultationId, int $patientId, int $doctorId): void
    {
        // Invalidate doctor's consultation list
        Cache::tags('consultations')->forget("consultation_list:doctor:{$doctorId}");

        // Invalidate patient's consultation list
        Cache::tags('consultations')->forget("consultation_list:patient:{$patientId}");

        // Invalidate rating cache (new rating may be added)
        Cache::tags('ratings')->forget("rating_average:doctor:{$doctorId}");

        Log::info('Cache invalidated on consultation ended', [
            'consultation_id' => $consultationId,
            'patient_id' => $patientId,
            'doctor_id' => $doctorId
        ]);
    }

    /**
     * Invalidate cache when prescription is added
     */
    public function onPrescriptionAdded(int $consultationId): void
    {
        // Invalidate prescription list for this consultation
        Cache::tags('prescriptions')->forget("prescription_list:{$consultationId}");

        Log::info('Cache invalidated on prescription added', [
            'consultation_id' => $consultationId
        ]);
    }

    /**
     * Invalidate cache when rating is added
     */
    public function onRatingAdded(int $doctorId, int $patientId): void
    {
        // Invalidate doctor's rating average
        Cache::tags('ratings')->forget("rating_average:doctor:{$doctorId}");

        // Invalidate doctor's statistics
        Cache::tags('dashboard')->forget("doctor_statistics:{$doctorId}");

        Log::info('Cache invalidated on rating added', [
            'doctor_id' => $doctorId,
            'patient_id' => $patientId
        ]);
    }

    /**
     * Invalidate cache when user profile is updated
     */
    public function onUserProfileUpdated(int $userId, string $userRole): void
    {
        // Invalidate user's own profile
        Cache::tags('users')->forget("user_profile:{$userId}");

        // If doctor, invalidate doctor-specific caches
        if ($userRole === 'dokter') {
            Cache::tags('doctors')->flush();
            Cache::tags('dashboard')->forget("doctor_statistics:{$userId}");
        }

        // If patient, invalidate patient-specific caches
        if ($userRole === 'pasien') {
            Cache::tags('patient_data')->forget("patient_appointments:{$userId}");
        }

        Log::info('Cache invalidated on user profile updated', [
            'user_id' => $userId,
            'user_role' => $userRole
        ]);
    }

    /**
     * Selectively invalidate by multiple criteria
     */
    public function invalidateByTags(array $tags): void
    {
        foreach ($tags as $tag) {
            Cache::tags($tag)->flush();
        }

        Log::info('Cache invalidated by tags', [
            'tags' => $tags
        ]);
    }

    /**
     * Bulk invalidate for significant events
     */
    public function invalidateAllForDoctor(int $doctorId): void
    {
        Cache::forget("slots:*:{$doctorId}:*");
        Cache::tags('appointments')->flush();
        Cache::tags('doctors')->flush();
        Cache::tags('dashboard')->flush();
        Cache::tags('consultations')->flush();
        Cache::tags('ratings')->flush();

        Log::warning('All doctor caches invalidated', [
            'doctor_id' => $doctorId
        ]);
    }

    /**
     * Bulk invalidate for significant events
     */
    public function invalidateAllForPatient(int $patientId): void
    {
        Cache::forget("patient_appointments:{$patientId}");
        Cache::forget("consultation_list:patient:{$patientId}");
        Cache::tags('patient_data')->flush();
        Cache::tags('consultations')->flush();

        Log::warning('All patient caches invalidated', [
            'patient_id' => $patientId
        ]);
    }

    /**
     * Safe cache invalidation with error handling
     */
    public function safeInvalidate(callable $invalidationLogic): bool
    {
        try {
            $invalidationLogic();
            return true;
        } catch (\Exception $e) {
            Log::error('Cache invalidation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
}
