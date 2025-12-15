<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Appointment Reminder Mail
 */
class AppointmentReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $appointment;

    public function __construct(Appointment $appointment)
    {
        $this->appointment = $appointment;
    }

    public function envelope()
    {
        return $this->subject('Appointment Reminder');
    }

    public function content()
    {
        return $this->markdown('emails.appointment-reminder');
    }

    public function attachments()
    {
        return [];
    }
}
