<?php

namespace App\Events;

use App\Models\Activity;
use App\Models\Actor;
use App\Models\Note;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NoteRepliedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $activity;
    public $actor;
    public $object;

    /**
     * Create a new event instance.
     */
    public function __construct($activity, $actor, $object)
    {
        $this->activity = $activity;
        $this->actor = $actor;
        $this->object = $object;
    }
}
