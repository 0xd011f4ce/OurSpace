<?php

namespace App\Listeners\AP;

use App\Models\Activity;
use App\Models\Follow;

use App\Types\TypeActivity;
use App\Types\TypeActor;

use App\Events\UserFollowedEvent;

use App\Events\AP\ActivityFollowEvent;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ActivityFollowListener
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
    public function handle(ActivityFollowEvent $event): void
    {
        if (TypeActivity::activity_exists ($event->activity["id"]))
            return;

        $actor = TypeActor::actor_exists_or_obtain ($event->activity ["actor"]);

        $target = TypeActor::actor_get_local ($event->activity ["object"]);
        if (!$target || !$target->user)
            return;

        // check follow doesn't exist
        $follow_exists = Follow::where ("actor", $actor->id)
            ->where ("object", $target->id)
            ->first ();
        if ($follow_exists)
            return;

        $event->activity ["activity_id"] = $event->activity ["id"];
        $act = Activity::create ($event->activity);

        UserFollowedEvent::dispatch ($act, $actor, $target);

        // TODO: Users should be able to manually check this
        $accept_activity = TypeActivity::craft_accept ($act);
        TypeActivity::post_activity ($accept_activity, $target, $actor);
    }
}
