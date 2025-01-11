<?php

namespace App\Listeners;

use App\Models\User;
use App\Models\Actor;

use App\Actions\ActionsFriends;

use App\Events\UserSignedUp;

use App\Notifications\UserNotification;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UserSignedUpListener
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
    public function handle(UserSignedUp $event): void
    {
        $actor = new Actor ();
        $actor->create_from_user ($event->user);

        // create a friendship between the new user and the admin
        $admin = User::where ("is_admin", 1)->first ();
        if ($admin)
        {
            ActionsFriends::force_friendship ($event->user, $admin);

            $admin->notify (new UserNotification(
                "Signup",
                $actor->id,
                $event->user->id
            ));
        }
    }
}
