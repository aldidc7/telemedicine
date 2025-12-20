<?php

namespace App\Jobs\Appointment;

use App\Services\Appointment\AppointmentReminderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * ============================================
 * JOB: CLEANUP OLD APPOINTMENT REMINDERS
 * ============================================
 * 
 * Run daily untuk delete old sent reminders
 * Keep database clean dan performant
 * 
 * Schedule di app/Console/Kernel.php:
 * $schedule->job(new CleanupOldReminders::class)->daily();
 */
class CleanupOldReminders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;

    public function handle(AppointmentReminderService $reminderService)
    {
        try {
            Log::info('CleanupOldReminders job started');

            $reminderService->cleanupOldReminders();

            Log::info('CleanupOldReminders job completed');
        } catch (\Exception $e) {
            Log::error('CleanupOldReminders job failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
