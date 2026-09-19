<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PortalNotification extends Notification
{
    use Queueable;

    public int $instituteId;
    public string $title;
    public string $message;
    public string $category;
    public string $icon;
    public string $color;
    public ?string $actionUrl;
    public ?string $actorName;
    public ?string $actorRole;
    public array $meta;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        int $instituteId,
        string $title,
        string $message,
        string $category = 'general',
        string $icon = 'bell',
        string $color = 'amber',
        ?string $actionUrl = null,
        ?string $actorName = null,
        ?string $actorRole = null,
        array $meta = []
    ) {
        $this->instituteId = $instituteId;
        $this->title = $title;
        $this->message = $message;
        $this->category = $category;
        $this->icon = $icon;
        $this->color = $color;
        $this->actionUrl = $actionUrl;
        $this->actorName = $actorName;
        $this->actorRole = $actorRole;
        $this->meta = $meta;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification stored in database.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'institute_id' => $this->instituteId,
            'title' => $this->title,
            'message' => $this->message,
            'category' => $this->category,
            'icon' => $this->icon,
            'color' => $this->color,
            'action_url' => $this->actionUrl,
            'actor_name' => $this->actorName,
            'actor_role' => $this->actorRole,
            'meta' => $this->meta,
            'created_at' => now()->toIso8601String(),
        ];
    }
}
