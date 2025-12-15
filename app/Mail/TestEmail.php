<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Test Email
 */
class TestEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function envelope()
    {
        return $this->subject('Test Email');
    }

    public function content()
    {
        return $this->markdown('emails.test');
    }

    public function attachments()
    {
        return [];
    }
}
