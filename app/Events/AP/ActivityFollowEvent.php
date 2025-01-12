<?php

namespace App\Events\AP;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ActivityFollowEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $activity;

    /**
     * Create a new event instance.
     */
    public function __construct($activity)
    {
        $this->activity = $activity;
    }
}
