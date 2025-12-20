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
 * JOB: RETRY FAILED APPOINTMENT REMINDERS
 * ============================================
 * 
 * Run setiap jam untuk retry reminders yang failed
 * Max 3 attempts sebelum give up
 * 
 * Schedule di app/Console/Kernel.php:
 * $schedule->job(new RetryFailedReminders::class)->hourly();
 */
class RetryFailedReminders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;

    public function handle(AppointmentReminderService $reminderService)
    {
        try {
            Log::info('RetryFailedReminders job started');

            $reminderService->retryFailedReminders();

            Log::info('RetryFailedReminders job completed');
        } catch (\Exception $e) {
            Log::error('RetryFailedReminders job failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
