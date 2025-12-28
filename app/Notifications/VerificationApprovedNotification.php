<?php

namespace App\Notifications;

use App\Models\DoctorVerification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Doctor Verification Approved Notification
 */
class VerificationApprovedNotification extends Notification implements ShouldQueue
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
            ->subject('Your Doctor Verification Has Been Approved')
            ->greeting("Hello Dr. {$notifiable->name},")
            ->line('Congratulations! Your doctor verification has been successfully approved.')
            ->line('You can now:')
            ->line('• Accept patient consultations')
            ->line('• Prescribe medicines')
            ->line('• Access all doctor features')
            ->action('View Your Profile', route('doctor.profile'))
            ->line('Thank you for joining our platform!')
            ->salutation('Best regards, ' . config('app.name'));
    }

    public function toDatabase($notifiable)
    {
        return [
            'type' => 'verification_approved',
            'verification_id' => $this->verification->id,
            'title' => 'Doctor Verification Approved',
            'message' => 'Your doctor verification has been approved. You can now accept consultations.',
            'approved_at' => $this->verification->verified_at,
        ];
    }
}
