<?php

namespace App\Events;

use App\Models\PasswordResetNotification;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a Student, Teacher, or Principal clicks "Forgot Password".
 *
 * For Students/Teachers → dispatches a real-time alert to the Principal's dashboard.
 * For Principals → dispatches to the Global Admin dashboard.
 *
 * Implements ShouldBroadcast for real-time notification via Laravel Broadcasting.
 */
class PasswordResetRequested implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public User $requestingUser,
        public PasswordResetNotification $notification
    ) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [];

        if ($this->notification->target_role === 'principal') {
            // Broadcast to the principal's private channel for this institute
            $channels[] = new PrivateChannel(
                "institute.{$this->requestingUser->institute_id}.notifications"
            );
        } else {
            // Broadcast to the global admin's channel
            $channels[] = new PrivateChannel('global-admin.notifications');
        }

        return $channels;
    }

    /**
     * Data that gets sent with the broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'notification_id' => $this->notification->id,
            'user_id'         => $this->requestingUser->id,
            'user_name'       => $this->requestingUser->name,
            'user_identifier' => $this->requestingUser->identifier,
            'user_role'       => $this->requestingUser->role,
            'institute_id'    => $this->requestingUser->institute_id,
            'target_role'     => $this->notification->target_role,
            'message'         => "{$this->requestingUser->name} has requested a password reset.",
            'created_at'      => $this->notification->created_at->toISOString(),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'password.reset.requested';
    }
}
