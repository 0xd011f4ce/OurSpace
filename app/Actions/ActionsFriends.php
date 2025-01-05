<?php

namespace App\Actions;

use GuzzleHttp\Client;

use App\Models\User;

use App\Types\TypeActor;
use Illuminate\Support\Facades\Log;

class ActionsFriends {
    public static function add_friend ($target)
    {
        if (!auth ()->check ())
            return ["error" => "You must be logged in to add friends."];

        $target_actor = TypeActor::actor_exists_or_obtain ($target);

        try {
            $client = new Client ();
            $response = $client->post (auth ()->user ()->actor->outbox, [
                "json" => [
                    "type" => "Follow",
                    "object" => $target
                ]
            ]);
        }
        catch (\Exception $e)
        {
            Log::error ("Error adding friend: " . $e->getMessage ());
            return ["error" => "Error adding friend"];
        }

        return ["success" => "Friend added"];
    }

    public static function force_friendship (User $user1, User $user2)
    {
        $actor1 = $user1->actor ()->first ();
        $actor2 = $user2->actor ()->first ();

        try {
            $client = new Client ();
            $response = $client->post ($actor1->outbox, [
                "json" => [
                    "type" => "Follow",
                    "object" => $actor2->actor_id
                ]
            ]);

            $response = $client->post ($actor2->outbox, [
                "json" => [
                    "type" => "Follow",
                    "object" => $actor1->actor_id
                ]
            ]);
        }
        catch (\Exception $e)
        {
            Log::error ("Error adding friend: " . $e->getMessage ());
            return ["error" => "Error adding friend"];
        }

        return ["success" => "Friend added"];
    }

    public static function remove_friend ($target)
    {
        if (!auth ()->check ())
            return ["error" => "You must be logged in to remove friends."];

        $target_actor = TypeActor::actor_exists_or_obtain ($target);

        try {
            $client = new Client ();
            $response = $client->post (auth ()->user ()->actor->outbox, [
                "json" => [
                    "type" => "Unfollow",
                    "object" => $target
                ]
            ]);
        }
        catch (\Exception $e)
        {
            Log::error ("Error removing friend: " . $e->getMessage ());
            return ["error" => "Error removing friend"];
        }

        return ["success" => "Friend removed"];
    }
}
