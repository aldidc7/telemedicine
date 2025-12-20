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
 * JOB: SEND APPOINTMENT REMINDERS
 * ============================================
 * 
 * Run setiap 5 menit untuk check dan send
 * appointment reminders yang scheduled
 * 
 * Schedule di app/Console/Kernel.php:
 * $schedule->job(new SendAppointmentReminders::class)->everyFiveMinutes();
 */
class SendAppointmentReminders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Job timeout (dalam detik)
     */
    public $timeout = 300; // 5 minutes

    public function handle(AppointmentReminderService $reminderService)
    {
        try {
            Log::info('SendAppointmentReminders job started');

            $reminderService->sendPendingReminders();

            Log::info('SendAppointmentReminders job completed');
        } catch (\Exception $e) {
            Log::error('SendAppointmentReminders job failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
