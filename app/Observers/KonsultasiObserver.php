<?php

namespace App\Observers;

use App\Models\Konsultasi;
use App\Services\Appointment\AppointmentReminderService;

/**
 * ============================================
 * KONSULTASI OBSERVER
 * ============================================
 * 
 * Automatically trigger reminder creation
 * when consultation is created/updated
 */
class KonsultasiObserver
{
    protected AppointmentReminderService $reminderService;

    public function __construct(AppointmentReminderService $reminderService)
    {
        $this->reminderService = $reminderService;
    }

    /**
     * Handle the Konsultasi "created" event.
     */
    public function created(Konsultasi $konsultasi): void
    {
        // Create reminders ketika appointment scheduled
        if ($konsultasi->appointment_time && $konsultasi->status === 'scheduled') {
            $this->reminderService->createReminders($konsultasi);
        }
    }

    /**
     * Handle the Konsultasi "updated" event.
     */
    public function updated(Konsultasi $konsultasi): void
    {
        // Recreate reminders jika appointment_time berubah
        if ($konsultasi->isDirty('appointment_time') && $konsultasi->status === 'scheduled') {
            // Delete old reminders
            $konsultasi->reminders()->delete();

            // Create new ones
            $this->reminderService->createReminders($konsultasi);
        }
    }

    /**
     * Handle the Konsultasi "deleted" event.
     */
    public function deleted(Konsultasi $konsultasi): void
    {
        // Delete associated reminders
        $konsultasi->reminders()->delete();
    }
}
