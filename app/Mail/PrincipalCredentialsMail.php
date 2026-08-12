<?php

namespace App\Mail;

use App\Models\Institute;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Sends login credentials to a newly created Principal.
 *
 * Dispatched automatically when Global Admin registers an institute
 * and creates the master login in the same flow.
 */
class PrincipalCredentialsMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $principal,
        public Institute $institute,
        public string $plainPassword
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Your UPLYFT Login Credentials — {$this->institute->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.principal-credentials',
        );
    }
}
