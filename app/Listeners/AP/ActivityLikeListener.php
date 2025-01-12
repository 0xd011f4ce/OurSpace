<?php

namespace App\Listeners\AP;

use App\Models\Note;
use App\Models\Activity;

use App\Types\TypeActivity;
use App\Types\TypeActor;

use App\Events\AP\ActivityLikeEvent;

use App\Events\NoteLikedEvent;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ActivityLikeListener
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
    public function handle(ActivityLikeEvent $event): void
    {
        $actor = TypeActor::actor_exists_or_obtain ($event->activity ["actor"]);
        $note_id = $event->activity ["object"];
        $note = Note::where ("note_id", $note_id)->first ();
        if (!$note)
        {
            return;
        }

        // check like doesn't already exist
        $like_exists = $actor->liked_note ($note);
        if ($like_exists)
            return;

        $event->activity ["activity_id"] = $event->activity ["id"];
        $activity_exists = TypeActivity::activity_exists ($event->activity ["id"]);
        if (!$activity_exists)
            $act = Activity::create ($event->activity);
        else
            $act = Activity::where ("activity_id", $event->activity ["id"])->first ();

        NoteLikedEvent::dispatch ($act, $actor, $note);

        return;
    }
}
