<?php

namespace App\Listeners;

use App\Events\UserFollowedEvent;

use App\Models\Follow;
use App\Models\Actor;
use App\Models\Activity;

use App\Notifications\UserNotification;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class UserFollowedListener
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
    public function handle(UserFollowedEvent $event): void
    {
        Follow::create ([
            "activity_id" => $event->activity->id,
            "actor" => $event->actor->id,
            "object" => $event->object->id
        ]);

        $user = $event->object->user;
        if (!$user)
            return;

        $user->notify (new UserNotification (
            "Follow",
            $event->actor->id,
            $event->object->id,
            $event->activity->id
        ));
    }
}
