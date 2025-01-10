<?php

namespace App\Events;

use App\Models\Actor;
use App\Models\Activity;
use App\Models\Note;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NoteLikedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Activity $activity;
    public Actor $actor;
    public Note $note;

    /**
     * Create a new event instance.
     */
    public function __construct(Activity $activity, Actor $actor, Note $note)
    {
        $this->activity = $activity;
        $this->actor = $actor;
        $this->note = $note;
    }
}
