<?php

namespace App\Events;

use App\Models\Blog;
use App\Models\User;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BlogCreatedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Blog $blog;
    public User $user;

    /**
     * Create a new event instance.
     */
    public function __construct(Blog $blog, User $user)
    {
        $this->blog = $blog;
        $this->user = $user;
    }
}
