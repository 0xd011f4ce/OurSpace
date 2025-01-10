<?php

namespace App\Listeners;

use App\Models\User;

use App\Events\NoteRepliedEvent;

use Illuminate\Support\Facades\Log;

use App\Notifications\UserNotification;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class NoteRepliedListener
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
    public function handle(NoteRepliedEvent $event): void
    {
        $note_actor = $event->object->get_actor ()->first ();

        Log::info ("hi");
        if (!$note_actor || !$note_actor->user)
            return;

        Log::info ("bai");

        $note_actor->user->notify (new UserNotification(
            "Reply",
            $event->actor->id,
            $event->object->id,
            $event->activity->id
        ));
    }
}
