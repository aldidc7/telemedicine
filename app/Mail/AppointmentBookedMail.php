<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Appointment Booked Notification Mail
 * Sent when an appointment is successfully booked
 */
class AppointmentBookedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var Appointment
     */
    public $appointment;

    /**
     * @var string|null
     */
    public $recipientType;

    /**
     * Create a new message instance.
     */
    public function __construct(Appointment $appointment, ?string $recipientType = null)
    {
        $this->appointment = $appointment;
        $this->recipientType = $recipientType;
    }

    /**
     * Get the message envelope.
     */
    public function envelope()
    {
        return $this->subject('Appointment Booked Confirmation');
    }

    /**
     * Get the message content definition.
     */
    public function content()
    {
        return $this->markdown('emails.appointment-booked');
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments()
    {
        return [];
    }
}
