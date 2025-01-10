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
    public $actor;
    public $object;

    /**
     * Create a new event instance.
     */
    public function __construct($activity)
    {
        $this->activity = $activity;

        $this->actor = TypeActor::actor_exists_or_obtain ($activity ["actor"]);

        $child_activity = $activity ["object"];
        $child_activity_id = "";

        if (is_array ($child_activity))
            $child_activity_id = $child_activity ["id"];
        else
            $child_activity_id = $child_activity;

        if (!TypeActivity::activity_exists ($child_activity_id))
            return ["error" => "Activity not found",];

        $child_activity = Activity::where ("activity_id", $child_activity_id)->first ();
        $this->object = $child_activity;

        switch ($this->object->type)
        {
            case "Follow":
                $unfollowed_actor = Actor::where ("actor_id", $this->object->object)->first ();
                UserUnfollowedEvent::dispatch ($this->object, $this->actor, $unfollowed_actor);
                break;
        }

        $child_activity->delete ();
    }
}
