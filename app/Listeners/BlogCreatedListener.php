<?php

namespace App\Listeners;

use App\Models\Actor;
use App\Types\TypeActor;

use App\Events\BlogCreatedEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class BlogCreatedListener
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
    public function handle(BlogCreatedEvent $event): void
    {
        $actor = new Actor ();
        $actor = $actor->create_from_blog ($event->blog);
        $actor->blog_id = $event->blog->id;
        $actor->user_id = $event->user->id;
        $actor->save ();

        $event->blog->actor_id = $actor->id;
        $event->blog->save ();
    }
}
