<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Generic Notification Mail
 */
class GenericNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $subject_line;
    public $body;

    public function __construct($user, string $subject, string $body)
    {
        $this->user = $user;
        $this->subject_line = $subject;
        $this->body = $body;
    }

    public function envelope()
    {
        return $this->subject($this->subject_line);
    }

    public function content()
    {
        return $this->markdown('emails.generic-notification');
    }

    public function attachments()
    {
        return [];
    }
}
