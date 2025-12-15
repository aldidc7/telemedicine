<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Appointment Cancelled Mail
 */
class AppointmentCancelledMail extends Mailable
{
    use Queueable, SerializesModels;

    public $appointment;
    public $reason;
    public $recipientType;

    public function __construct(Appointment $appointment, ?string $reason = null, ?string $recipientType = null)
    {
        $this->appointment = $appointment;
        $this->reason = $reason;
        $this->recipientType = $recipientType;
    }

    public function envelope()
    {
        return $this->subject('Appointment Cancelled');
    }

    public function content()
    {
        return $this->markdown('emails.appointment-cancelled');
    }

    public function attachments()
    {
        return [];
    }
}
