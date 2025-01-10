<?php

namespace App\Events;

use App\Models\Activity;
use App\Models\Actor;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserUnfollowedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Activity $activity;
    public Actor $actor;
    public Actor $object;

    /**
     * Create a new event instance.
     */
    public function __construct(Activity $activity, Actor $actor, Actor $object)
    {
        $this->activity = $activity;
        $this->actor = $actor;
        $this->object = $object;
    }
}
