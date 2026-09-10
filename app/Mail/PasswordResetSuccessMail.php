<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Mail sent immediately when a user's password has been reset.
 * Contains:
 * - The new temporary/updated password
 * - Direct login link for their appropriate portal
 * - Instructions to change password after logging in
 */
class PasswordResetSuccessMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $newPassword,
        public ?User $processedBy = null
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '✅ Your UPLYFT Password Has Been Reset',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.password-reset-success',
        );
    }
}
