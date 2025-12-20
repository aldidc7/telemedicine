<?php

namespace App\Services\Appointment;

use App\Models\Konsultasi;
use App\Models\AppointmentReminder;
use App\Models\User;
use App\Events\AppointmentReminderNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * ============================================
 * APPOINTMENT REMINDER SERVICE
 * ============================================
 * 
 * Handle appointment reminder workflow:
 * - Create reminders ketika appointment dibuat
 * - Send reminders pada scheduled time
 * - Retry failed reminders
 * - Track reminder status untuk audit
 * 
 * Channels:
 * - SMS (via Twilio)
 * - Email
 * - Push Notification
 */
class AppointmentReminderService
{
    /**
     * Create reminders untuk appointment
     * 
     * Dibuat saat appointment dibuat dengan scheduling:
     * - 24 jam sebelum: Email reminder
     * - 2 jam sebelum: SMS + Push notification
     * 
     * @param Konsultasi $appointment
     * @return void
     */
    public function createReminders(Konsultasi $appointment): void
    {
        try {
            $patient = $appointment->patient;
            $appointmentTime = $appointment->appointment_time;

            // Email reminder: 24 jam sebelum
            if ($this->shouldSendEmail($patient)) {
                AppointmentReminder::create([
                    'appointment_id' => $appointment->id,
                    'user_id' => $patient->id,
                    'reminder_type' => 'email',
                    'scheduled_for' => $appointmentTime->copy()->subHours(24),
                    'status' => 'pending',
                ]);
            }

            // SMS reminder: 2 jam sebelum
            if ($this->shouldSendSMS($patient)) {
                AppointmentReminder::create([
                    'appointment_id' => $appointment->id,
                    'user_id' => $patient->id,
                    'reminder_type' => 'sms',
                    'scheduled_for' => $appointmentTime->copy()->subHours(2),
                    'status' => 'pending',
                ]);
            }

            // Push notification: 1 jam sebelum
            if ($this->shouldSendPush($patient)) {
                AppointmentReminder::create([
                    'appointment_id' => $appointment->id,
                    'user_id' => $patient->id,
                    'reminder_type' => 'push',
                    'scheduled_for' => $appointmentTime->copy()->subHours(1),
                    'status' => 'pending',
                ]);
            }

            Log::info("Appointment reminders created for appointment {$appointment->id}");
        } catch (\Exception $e) {
            Log::error("Failed to create appointment reminders: {$e->getMessage()}");
        }
    }

    /**
     * Send all pending reminders yang sudah scheduled
     * 
     * Dijalankan oleh job scheduler setiap 5 menit
     */
    public function sendPendingReminders(): void
    {
        try {
            $reminders = AppointmentReminder::readyToSend()->get();

            foreach ($reminders as $reminder) {
                // Skip jika appointment sudah lewat
                if ($reminder->isAppointmentExpired()) {
                    $reminder->update(['status' => 'cancelled']);
                    continue;
                }

                $this->sendReminder($reminder);
            }

            Log::info("Processed " . count($reminders) . " appointment reminders");
        } catch (\Exception $e) {
            Log::error("Error in sendPendingReminders: {$e->getMessage()}");
        }
    }

    /**
     * Send individual reminder
     */
    public function sendReminder(AppointmentReminder $reminder): void
    {
        try {
            $appointment = $reminder->appointment;
            $user = $reminder->user;

            match ($reminder->reminder_type) {
                'email' => $this->sendEmailReminder($reminder, $user, $appointment),
                'sms' => $this->sendSMSReminder($reminder, $user, $appointment),
                'push' => $this->sendPushReminder($reminder, $user, $appointment),
                default => throw new \Exception("Unknown reminder type: {$reminder->reminder_type}"),
            };

            $reminder->markAsSent();
            Log::info("Reminder {$reminder->id} sent successfully");
        } catch (\Exception $e) {
            $reminder->markAsFailed($e->getMessage());
            Log::warning("Reminder {$reminder->id} failed: {$e->getMessage()}");
        }
    }

    /**
     * Send email reminder
     */
    private function sendEmailReminder(AppointmentReminder $reminder, User $user, Konsultasi $appointment): void
    {
        if (!$user->email) {
            throw new \Exception("User {$user->id} has no email");
        }

        $appointmentTime = $appointment->appointment_time->format('d M Y H:i');
        $doctorName = $appointment->dokter->user->name;

        Mail::send('emails.appointment-reminder', [
            'user' => $user,
            'appointment' => $appointment,
            'doctor' => $appointment->dokter,
            'appointmentTime' => $appointmentTime,
        ], function ($m) use ($user) {
            $m->to($user->email)
                ->subject('Reminder: Appointment Anda dengan Dokter');
        });
    }

    /**
     * Send SMS reminder via Twilio
     */
    private function sendSMSReminder(AppointmentReminder $reminder, User $user, Konsultasi $appointment): void
    {
        if (!$user->phone) {
            throw new \Exception("User {$user->id} has no phone number");
        }

        $appointmentTime = $appointment->appointment_time->format('d M H:i');
        $doctorName = $appointment->dokter->user->name;

        $message = "Reminder: Konsultasi Anda dengan {$doctorName} pada {$appointmentTime}. "
            . "Siap-siap 5 menit sebelumnya. Chat link sudah dikirim.";

        // Use SMS service
        app('sms')->send($user->phone, $message);
    }

    /**
     * Send push notification
     */
    private function sendPushReminder(AppointmentReminder $reminder, User $user, Konsultasi $appointment): void
    {
        $appointmentTime = $appointment->appointment_time->format('H:i');
        $doctorName = $appointment->dokter->user->name;

        // Use broadcasting atau push service
        broadcast(new AppointmentReminderNotification(
            user: $user,
            title: "Appointment dalam 1 jam",
            message: "Konsultasi dengan {$doctorName} di {$appointmentTime}",
            appointment: $appointment
        ))->toOthers();
    }

    /**
     * Retry failed reminders
     * 
     * Run setiap jam untuk retry failed reminders
     */
    public function retryFailedReminders(): void
    {
        try {
            $failedReminders = AppointmentReminder::failed()
                ->where('retry_count', '<', 3)
                ->where('updated_at', '<', now()->subHours(1))
                ->get();

            foreach ($failedReminders as $reminder) {
                $reminder->update(['status' => 'pending']);
                $this->sendReminder($reminder);
            }

            Log::info("Retried " . count($failedReminders) . " failed reminders");
        } catch (\Exception $e) {
            Log::error("Error in retryFailedReminders: {$e->getMessage()}");
        }
    }

    /**
     * Delete old completed reminders (cleanup)
     * 
     * Run daily untuk keep database clean
     */
    public function cleanupOldReminders(): void
    {
        $deleted = AppointmentReminder::sent()
            ->where('sent_at', '<', now()->subDays(90))
            ->delete();

        Log::info("Deleted $deleted old appointment reminders");
    }

    /**
     * Check if user wants email reminders
     */
    private function shouldSendEmail(User $user): bool
    {
        return $user->email && $user->email_notifications_enabled;
    }

    /**
     * Check if user wants SMS reminders
     */
    private function shouldSendSMS(User $user): bool
    {
        return $user->phone && $user->sms_notifications_enabled;
    }

    /**
     * Check if user wants push notifications
     */
    private function shouldSendPush(User $user): bool
    {
        return $user->push_notifications_enabled ?? true; // Default enabled
    }
}
