<?php

namespace App\Notifications;

use App\Models\DoctorVerification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Doctor Verification Rejected Notification
 */
class VerificationRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected DoctorVerification $verification;

    public function __construct(DoctorVerification $verification)
    {
        $this->verification = $verification;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Doctor Verification Status Update')
            ->greeting("Hello Dr. {$notifiable->name},")
            ->line('Your doctor verification request has been reviewed.')
            ->line('Unfortunately, we were unable to verify your documents at this time.')
            ->line('')
            ->line('**Reason for Rejection:**')
            ->line($this->verification->notes ?? 'Please contact support for details.')
            ->line('')
            ->line('**What to do next:**')
            ->line('• Review the rejection reason carefully')
            ->line('• Prepare correct/updated documents')
            ->line('• Resubmit your verification request')
            ->action('Resubmit Verification', route('doctor.verification.submit'))
            ->line('If you have questions, please contact our support team.')
            ->salutation('Best regards, ' . config('app.name'));
    }

    public function toDatabase($notifiable)
    {
        return [
            'type' => 'verification_rejected',
            'verification_id' => $this->verification->id,
            'title' => 'Doctor Verification Rejected',
            'message' => 'Your doctor verification was rejected. Reason: ' . ($this->verification->notes ?? 'N/A'),
            'rejection_reason' => $this->verification->notes,
            'rejected_at' => now(),
        ];
    }
}
