<?php

namespace App\Listeners\AP;

use App\Models\Activity;
use App\Models\Actor;

use App\Types\TypeActor;
use App\Types\TypeActivity;

use App\Events\AP\ActivityUndoEvent;
use App\Events\UserUnfollowedEvent;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ActivityUndoListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ActivityUndoEvent $event): void
    {
        $actor = TypeActor::actor_exists_or_obtain ($event->activity ["actor"]);

        $child_activity = $event->activity ["object"];
        $child_activity_id = "";

        if (is_array ($child_activity))
            $child_activity_id = $child_activity ["id"];
        else
            $child_activity_id = $child_activity;

        if (!TypeActivity::activity_exists ($child_activity_id))
            return;

        $child_activity = Activity::where ("activity_id", $child_activity_id)->first ();
        $object = $child_activity;

        switch ($object->type)
        {
            case "Follow":
                $unfollowed_actor = Actor::where ("actor_id", $object->object)->first ();
                UserUnfollowedEvent::dispatch ($object, $actor, $unfollowed_actor);
                break;
        }

        $child_activity->delete ();
    }
}
