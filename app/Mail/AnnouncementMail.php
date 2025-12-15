<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Announcement Mail
 */
class AnnouncementMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $title;
    public $message;

    public function __construct($user, string $title, string $message)
    {
        $this->user = $user;
        $this->title = $title;
        $this->message = $message;
    }

    public function envelope()
    {
        return $this->subject($this->title);
    }

    public function content()
    {
        return $this->markdown('emails.announcement');
    }

    public function attachments()
    {
        return [];
    }
}
