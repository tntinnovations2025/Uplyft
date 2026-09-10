<?php

namespace App\Mail;

use App\Models\PasswordResetNotification;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Mail sent immediately when a user requests a password reset.
 * Contains:
 * - 6-Digit Verification OTP
 * - Notice alerting user if they did not initiate request
 * - One-click cancellation link to immediately void the request
 */
class PasswordResetRequestedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public PasswordResetNotification $notification
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🔑 Security Alert & Password Reset OTP — UPLYFT',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.password-reset-requested',
        );
    }
}
