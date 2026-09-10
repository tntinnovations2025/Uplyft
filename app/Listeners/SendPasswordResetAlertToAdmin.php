<?php

namespace App\Listeners;

use App\Events\PasswordResetRequested;
use App\Models\User;
use App\Notifications\PasswordResetAlert;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

/**
 * Sends an in-app notification to the appropriate admin
 * when a password reset request is filed.
 */
class SendPasswordResetAlertToAdmin implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(PasswordResetRequested $event): void
    {
        $notification = $event->notification;

        if ($notification->target_role === 'principal') {
            // Notify all principals in the same institute
            $admins = User::where('institute_id', $event->requestingUser->institute_id)
                ->where('role', User::ROLE_PRINCIPAL)
                ->get();
        } else {
            // Notify all global admins
            $admins = User::where('role', User::ROLE_GLOBAL_ADMIN)->get();
        }

        foreach ($admins as $admin) {
            try {
                $admin->notify(new PasswordResetAlert($event->requestingUser, $notification));
            } catch (\Throwable $e) {
                Log::error('Failed to send password reset alert', [
                    'admin_id'        => $admin->id,
                    'notification_id' => $notification->id,
                    'error'           => $e->getMessage(),
                ]);
            }
        }
    }
}
