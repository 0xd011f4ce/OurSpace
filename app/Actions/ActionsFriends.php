<?php

namespace App\Actions;

use GuzzleHttp\Client;

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
