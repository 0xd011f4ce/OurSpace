<?php

namespace App\Listeners;

use App\Models\Like;

use App\Events\NoteLikedEvent;

use Illuminate\Support\Facades\Log;
use App\Notifications\UserNotification;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class NoteLikedListener
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
    public function handle(NoteLikedEvent $event): void
    {
        $like = Like::create ([
            "activity_id" => $event->activity->id,
            "actor_id" => $event->actor->id,
            "note_id" => $event->note->id
        ]);

        $user = $event->note->get_actor ()->first ()->user;
        if (!$user)
            return;

        $user->notify (new UserNotification(
            "Like",
            $event->actor->id,
            $event->note->id,
            $event->activity->id
        ));
    }
}
