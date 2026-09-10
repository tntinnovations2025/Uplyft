<?php

namespace App\Notifications;

use App\Models\PasswordResetNotification as ResetNotification;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

/**
 * In-app notification sent to admins when a user requests a password reset.
 * Delivered via database and broadcast channels.
 */
class PasswordResetAlert extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected User $requestingUser,
        protected ResetNotification $resetNotification
    ) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation (stored in database notifications table).
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type'            => 'password_reset_request',
            'notification_id' => $this->resetNotification->id,
            'user_id'         => $this->requestingUser->id,
            'user_name'       => $this->requestingUser->name,
            'user_identifier' => $this->requestingUser->identifier,
            'user_role'       => $this->requestingUser->role,
            'institute_id'    => $this->requestingUser->institute_id,
            'message'         => "{$this->requestingUser->name} ({$this->requestingUser->identifier}) has requested a password reset.",
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
