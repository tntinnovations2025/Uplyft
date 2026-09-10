<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Emails the 6-digit OTP needed to verify a password change
 * requested from the user's personal settings.
 */
class PasswordChangeOtpMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $otp,
        public ?string $fromEmail = null,
        public ?string $fromName = null
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address($this->fromEmail ?: config('mail.from.address'), $this->fromName ?: config('mail.from.name')),
            subject: 'Your UPLYFT Password Change OTP',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.password-change-otp',
        );
    }
}