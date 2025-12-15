<?php

namespace App\Services;

use App\Models\User;
use App\Models\Appointment;
use App\Models\Consultation;
use App\Mail\AppointmentBookedMail;
use App\Mail\AppointmentConfirmedMail;
use App\Mail\AppointmentCancelledMail;
use App\Mail\ConsultationStartedMail;
use App\Mail\ConsultationEndedMail;
use App\Mail\PrescriptionCreatedMail;
use App\Mail\RatingReceivedMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

/**
 * Email Notification Service
 * 
 * Manages all email notifications sent to users
 * Appointment confirmations, reminders, consultations, prescriptions
 */
class EmailNotificationService
{
    /**
     * Send appointment booked notification
     */
    public function sendAppointmentBookedNotification(Appointment $appointment): bool
    {
        try {
            // Notify patient
            Mail::to($appointment->patient->email)->send(
                new AppointmentBookedMail($appointment)
            );

            // Notify doctor
            Mail::to($appointment->doctor->email)->send(
                new AppointmentBookedMail($appointment, 'doctor')
            );

            Log::info('Appointment booked notifications sent', [
                'appointment_id' => $appointment->id,
                'patient_email' => $appointment->patient->email,
                'doctor_email' => $appointment->doctor->email,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send appointment booked notifications', [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Send appointment confirmed notification
     */
    public function sendAppointmentConfirmedNotification(Appointment $appointment): bool
    {
        try {
            // Notify patient
            Mail::to($appointment->patient->email)->send(
                new AppointmentConfirmedMail($appointment)
            );

            Log::info('Appointment confirmed notification sent', [
                'appointment_id' => $appointment->id,
                'patient_email' => $appointment->patient->email,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send appointment confirmed notification', [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Send appointment cancelled notification
     */
    public function sendAppointmentCancelledNotification(
        Appointment $appointment,
        string $reason = null
    ): bool {
        try {
            // Notify patient
            Mail::to($appointment->patient->email)->send(
                new AppointmentCancelledMail($appointment, $reason, 'patient')
            );

            // Notify doctor
            Mail::to($appointment->doctor->email)->send(
                new AppointmentCancelledMail($appointment, $reason, 'doctor')
            );

            Log::info('Appointment cancelled notifications sent', [
                'appointment_id' => $appointment->id,
                'reason' => $reason,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send appointment cancelled notifications', [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Send appointment reminder (24 hours before)
     */
    public function sendAppointmentReminder(Appointment $appointment): bool
    {
        try {
            // Send to patient
            Mail::to($appointment->patient->email)->send(
                new \App\Mail\AppointmentReminderMail($appointment)
            );

            Log::info('Appointment reminder sent', [
                'appointment_id' => $appointment->id,
                'patient_email' => $appointment->patient->email,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send appointment reminder', [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Send consultation started notification
     */
    public function sendConsultationStartedNotification(Consultation $consultation): bool
    {
        try {
            $appointment = $consultation->appointment;

            // Notify patient
            Mail::to($appointment->patient->email)->send(
                new ConsultationStartedMail($consultation)
            );

            Log::info('Consultation started notification sent', [
                'consultation_id' => $consultation->id,
                'patient_email' => $appointment->patient->email,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send consultation started notification', [
                'consultation_id' => $consultation->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Send consultation ended notification
     */
    public function sendConsultationEndedNotification(Consultation $consultation): bool
    {
        try {
            $appointment = $consultation->appointment;

            // Notify patient with consultation summary
            Mail::to($appointment->patient->email)->send(
                new ConsultationEndedMail($consultation)
            );

            // Notify doctor
            Mail::to($appointment->doctor->email)->send(
                new ConsultationEndedMail($consultation, 'doctor')
            );

            Log::info('Consultation ended notifications sent', [
                'consultation_id' => $consultation->id,
                'patient_email' => $appointment->patient->email,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send consultation ended notifications', [
                'consultation_id' => $consultation->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Send prescription notification
     */
    public function sendPrescriptionNotification(
        Consultation $consultation,
        array $prescriptionData
    ): bool {
        try {
            $appointment = $consultation->appointment;

            // Notify patient
            Mail::to($appointment->patient->email)->send(
                new PrescriptionCreatedMail($consultation, $prescriptionData)
            );

            Log::info('Prescription notification sent', [
                'consultation_id' => $consultation->id,
                'patient_email' => $appointment->patient->email,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send prescription notification', [
                'consultation_id' => $consultation->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Send rating received notification to doctor
     */
    public function sendRatingReceivedNotification(
        int $doctorId,
        int $rating,
        string $comment = null
    ): bool {
        try {
            $doctor = User::find($doctorId);

            if (!$doctor) {
                return false;
            }

            Mail::to($doctor->email)->send(
                new RatingReceivedMail($doctor, $rating, $comment)
            );

            Log::info('Rating received notification sent', [
                'doctor_id' => $doctorId,
                'doctor_email' => $doctor->email,
                'rating' => $rating,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send rating received notification', [
                'doctor_id' => $doctorId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Send welcome email to new user
     */
    public function sendWelcomeEmail(User $user): bool
    {
        try {
            Mail::to($user->email)->send(
                new \App\Mail\WelcomeEmail($user)
            );

            Log::info('Welcome email sent', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send welcome email', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Send bulk notifications to users
     */
    public function sendBulkNotification(array $userIds, string $subject, string $body): int
    {
        $users = User::whereIn('id', $userIds)->get();
        $sentCount = 0;

        foreach ($users as $user) {
            try {
                Mail::to($user->email)->send(
                    new \App\Mail\GenericNotificationMail($user, $subject, $body)
                );
                $sentCount++;
            } catch (\Exception $e) {
                Log::warning('Failed to send bulk notification to user', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('Bulk notifications sent', [
            'total_users' => count($userIds),
            'sent_count' => $sentCount,
        ]);

        return $sentCount;
    }

    /**
     * Send system announcement
     */
    public function sendSystemAnnouncement(string $title, string $message, string $role = null): int
    {
        $query = User::query();

        if ($role) {
            $query->where('role', $role);
        }

        $users = $query->get();
        $sentCount = 0;

        foreach ($users as $user) {
            try {
                Mail::to($user->email)->send(
                    new \App\Mail\AnnouncementMail($user, $title, $message)
                );
                $sentCount++;
            } catch (\Exception $e) {
                Log::warning('Failed to send announcement', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('System announcement sent', [
            'title' => $title,
            'role' => $role,
            'sent_count' => $sentCount,
        ]);

        return $sentCount;
    }

    /**
     * Queue email for later sending
     */
    public function queueEmail(
        string $email,
        string $mailClass,
        array $parameters = []
    ): bool {
        try {
            // Instantiate mail class if it's a string class name
            if (is_string($mailClass) && class_exists($mailClass)) {
                $mailable = new $mailClass(...array_values($parameters));
            } else {
                $mailable = $mailClass;
            }
            
            Mail::to($email)->queue($mailable);

            Log::info('Email queued', [
                'email' => $email,
                'mail_class' => get_class($mailable),
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to queue email', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Send test email
     */
    public function sendTestEmail(string $email): bool
    {
        try {
            Mail::to($email)->send(
                new \App\Mail\TestEmail()
            );

            Log::info('Test email sent', [
                'email' => $email,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send test email', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Check email configuration
     */
    public function validateEmailConfiguration(): array
    {
        return [
            'driver' => config('mail.driver'),
            'from_address' => config('mail.from.address'),
            'from_name' => config('mail.from.name'),
            'status' => $this->checkEmailService(),
        ];
    }

    /**
     * Check if email service is working
     */
    public function checkEmailService(): string
    {
        try {
            $this->sendTestEmail('test@example.com');
            return 'ok';
        } catch (\Exception $e) {
            return 'error: ' . $e->getMessage();
        }
    }
}
