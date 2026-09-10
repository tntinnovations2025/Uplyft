<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Custom password-reset email notification for the UPLYFT / Principal Portal.
 *
 * Overrides Laravel's default reset email (`laravel::reset-password`) with a
 * branded, responsive HTML template. Dispatched automatically via the User
 * model's `sendPasswordResetNotification($token)` hook, which the Password
 * Broker calls after generating a token.
 *
 * Delivered synchronously (QUEUE_CONNECTION=sync) for low-volume traffic;
 * swap to 'database' + a worker if you scale up.
 */
class ResetPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The reset token.
     */
    public string $token;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return string[]
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Build the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        // Laravel's Password Broker generates the reset URL via 'password.reset'
        // using the broker's configured 'expire' value (60 min in config/auth.php).
        $resetUrl = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        // Determine the portal login URL so the user can sign in after reseting.
        $loginUrl = '/login';
        if (method_exists($notifiable, 'isPrincipal') && $notifiable->isPrincipal()) {
            $loginUrl = '/principal/login';
        } elseif (method_exists($notifiable, 'isGlobalAdmin') && $notifiable->isGlobalAdmin()) {
            $loginUrl = '/global-admin/login';
        }

        return (new MailMessage)
            ->subject('Reset your ' . config('app.name', 'Principal Portal') . ' password')
            // Reference the Blade template instead of the default markdown.
            ->view('emails.reset-password', [
                'user'        => $notifiable,
                'resetUrl'    => $resetUrl,
                'loginUrl'    => $loginUrl,
                'expiresIn'   => config('auth.passwords.' . config('auth.defaults.passwords') . '.expire', 60), // minutes
                'appName'     => config('app.name', 'Principal Portal'),
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'email' => $notifiable->getEmailForPasswordReset(),
        ];
    }
}