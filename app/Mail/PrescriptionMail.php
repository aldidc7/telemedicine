<?php

namespace App\Mail;

use App\Models\Prescription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

/**
 * Prescription Mail
 * Sends prescription via email with PDF attachment
 */
class PrescriptionMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Prescription $prescription;
    public string $pdfContent;

    /**
     * Create a new message instance.
     */
    public function __construct(Prescription $prescription, string $pdfContent = '')
    {
        $this->prescription = $prescription;
        $this->pdfContent = $pdfContent;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $doctorName = $this->prescription->doctor->name ?? 'Doctor';
        
        return new Envelope(
            subject: "Prescription from {$doctorName}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.prescription',
            with: [
                'prescription' => $this->prescription,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        if (!$this->pdfContent) {
            return [];
        }

        return [
            Attachment::fromData(fn () => $this->pdfContent, "prescription-{$this->prescription->id}.pdf")
                ->withMime('application/pdf'),
        ];
    }
}
