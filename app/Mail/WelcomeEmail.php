<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Welcome Email
 */
class WelcomeEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function envelope()
    {
        return $this->subject('Welcome to Telemedicine Platform');
    }

    public function content()
    {
        return $this->markdown('emails.welcome');
    }

    public function attachments()
    {
        return [];
    }
}
