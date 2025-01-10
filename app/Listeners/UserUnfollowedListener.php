<?php

namespace App\Listeners;

use App\Models\Actor;
use App\Models\Follow;

use App\Notifications\UserNotification;

use App\Events\UserUnfollowedEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class UserUnfollowedListener
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
    public function handle(UserUnfollowedEvent $event): void
    {
        $follow_exists = Follow::where("actor_id", $event->actor->id)
            ->where("object_id", $event->object->id)
            ->first();

        if ($follow_exists)
            $follow_exists->delete ();

        $user = $event->object->user;
        if (!$user)
            return;

        $user->notify(new UserNotification(
            "Unfollow",
            $event->actor->id,
            $event->object->id,
            $event->activity->id
        ));
    }
}
