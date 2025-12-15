<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Prescription Created Mail
 */
class PrescriptionCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $consultation;
    public $prescriptionData;

    public function __construct($consultation, array $prescriptionData)
    {
        $this->consultation = $consultation;
        $this->prescriptionData = $prescriptionData;
    }

    public function envelope()
    {
        return $this->subject('Prescription Created');
    }

    public function content()
    {
        return $this->markdown('emails.prescription-created');
    }

    public function attachments()
    {
        return [];
    }
}
