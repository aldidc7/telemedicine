<?php

namespace App\Mail;

use App\Models\Consultation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Consultation Started Mail
 */
class ConsultationStartedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $consultation;

    public function __construct(Consultation $consultation)
    {
        $this->consultation = $consultation;
    }

    public function envelope()
    {
        return $this->subject('Consultation Started');
    }

    public function content()
    {
        return $this->markdown('emails.consultation-started');
    }

    public function attachments()
    {
        return [];
    }
}
