<?php

namespace App\Http\Controllers\AP;

use App\Models\User;
use App\Models\Actor;
use App\Models\Activity;

use App\Types\TypeActivity;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class APOutboxController extends Controller
{
    public function outbox (User $user, Request $request)
    {
        switch ($request->get ("type"))
        {
            case "Follow":
                return $this->handle_follow ($user, $request->get ("object"));
                break;

            case "Unfollow":
                return $this->handle_unfollow ($user, $request->get ("object"));
                break;

            default:
                Log::info ("APOutboxController@index");
                Log::info (json_encode (request ()->all ()));
                break;
        }
    }

    public function handle_follow (User $user, string $object)
    {
        $object_actor = Actor::where ("actor_id", $object)->first ();
        if (!$object_actor)
            return response ()->json ([ "error" => "object not found" ], 404);

        $follow_activity = TypeActivity::craft_follow ($user->actor ()->first (), $object_actor);
        $response = TypeActivity::post_activity ($follow_activity, $user->actor ()->first (), $object_actor);

        if ($response->getStatusCode () < 200 || $response->getStatusCode () >= 300)
            return response ()->json ([ "error" => "failed to post activity" ], 500);

        return [
            "success" => "followed"
        ];
    }

    public function handle_unfollow (User $user, string $object)
    {
        $object_actor = Actor::where ("actor_id", $object)->first ();
        if (!$object_actor)
            return response ()->json ([ "error" => "object not found" ], 404);
        $object_id = '"' . str_replace ("/", "\/", $object_actor->actor_id) . '"';

        $follow_activity = Activity::where ("actor", $user->actor ()->first ()->actor_id)
            ->where ("object", $object_id)
            ->where ("type", "Follow")
            ->first ();
        if (!$follow_activity)
            return response ()->json ([ "error" => "no follow activity found" ], 404);

        $unfollow_activity = TypeActivity::craft_undo ($follow_activity, $user->actor ()->first ());
        $response = TypeActivity::post_activity ($unfollow_activity, $user->actor ()->first (), $object_actor);

        if ($response->getStatusCode () < 200 || $response->getStatusCode () >= 300)
            return response ()->json ([ "error" => "failed to post activity" ], 500);

        $follow_activity->delete ();
        return [
            "success" => "unfollowed"
        ];
    }
}
