<?php

namespace App\Mail;

use App\Models\Consultation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Consultation Ended Mail
 */
class ConsultationEndedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $consultation;
    public $recipientType;

    public function __construct(Consultation $consultation, ?string $recipientType = null)
    {
        $this->consultation = $consultation;
        $this->recipientType = $recipientType;
    }

    public function envelope()
    {
        return $this->subject('Consultation Ended');
    }

    public function content()
    {
        return $this->markdown('emails.consultation-ended');
    }

    public function attachments()
    {
        return [];
    }
}
