<?php

namespace App\Mail;

use App\Models\Dokter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DoctorRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Dokter $doctor,
        public string $reason
    ) {
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Update Pendaftaran Dokter - Aplikasi Telemedicine',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.doctor-rejected',
            with: [
                'doctor' => $this->doctor,
                'doctorName' => $this->doctor->user->name,
                'reason' => $this->reason,
                'contactEmail' => config('mail.from.address'),
            ],
        );
    }
}
