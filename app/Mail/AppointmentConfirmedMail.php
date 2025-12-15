<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Appointment Confirmed Mail
 */
class AppointmentConfirmedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $appointment;

    public function __construct(Appointment $appointment)
    {
        $this->appointment = $appointment;
    }

    public function envelope()
    {
        return $this->subject('Appointment Confirmed');
    }

    public function content()
    {
        return $this->markdown('emails.appointment-confirmed');
    }

    public function attachments()
    {
        return [];
    }
}
