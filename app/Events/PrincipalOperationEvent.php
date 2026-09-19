<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PrincipalOperationEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $instituteId;
    public string $id;
    public string $title;
    public string $message;
    public string $category;
    public string $icon;
    public string $color;
    public ?string $actionUrl;
    public ?string $actorName;
    public ?string $actorRole;
    public array $meta;
    public string $createdAt;
    public string $createdAtHuman;

    /**
     * Create a new event instance.
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
        array $meta = [],
        ?string $id = null
    ) {
        $this->instituteId = $instituteId;
        $this->id = $id ?? (string) \Illuminate\Support\Str::uuid();
        $this->title = $title;
        $this->message = $message;
        $this->category = $category;
        $this->icon = $icon;
        $this->color = $color;
        $this->actionUrl = $actionUrl;
        $this->actorName = $actorName;
        $this->actorRole = $actorRole;
        $this->meta = $meta;
        $this->createdAt = now()->toIso8601String();
        $this->createdAtHuman = 'Just now';
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('institute.' . $this->instituteId . '.principal'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'operation.performed';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->id,
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
            'created_at' => $this->createdAt,
            'created_at_human' => $this->createdAtHuman,
            'read_at' => null,
        ];
    }
}
