<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Rating Received Mail
 */
class RatingReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $doctor;
    public $rating;
    public $comment;

    public function __construct($doctor, $rating, ?string $comment = null)
    {
        $this->doctor = $doctor;
        $this->rating = $rating;
        $this->comment = $comment;
    }

    public function envelope()
    {
        return $this->subject('New Rating Received');
    }

    public function content()
    {
        return $this->markdown('emails.rating-received');
    }

    public function attachments()
    {
        return [];
    }
}
