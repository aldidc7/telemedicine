<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VerifyEmailMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(private User $user)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Verifikasi Email - Aplikasi Telemedicine',
        );
    }

    public function content(): Content
    {
        $verificationUrl = config('app.url') . '/verify-email?token=' . $this->user->email_verification_token;
        
        return new Content(
            view: 'emails.verify-email',
            with: [
                'user' => $this->user,
                'verificationUrl' => $verificationUrl,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
