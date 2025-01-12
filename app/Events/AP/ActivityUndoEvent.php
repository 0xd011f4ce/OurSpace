<?php

namespace App\Events\AP;

use App\Models\Activity;
use App\Models\Actor;

use App\Types\TypeActivity;
use App\Types\TypeActor;

use App\Events\UserUnfollowedEvent;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ActivityUndoEvent
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
