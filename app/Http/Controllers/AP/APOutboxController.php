<?php

namespace App\Http\Controllers\AP;

use App\Models\User;
use App\Models\Actor;
use App\Models\Activity;
use App\Models\Instance;
use App\Models\Note;
use App\Models\NoteAttachment;

use App\Types\TypeActivity;
use App\Types\TypeActor;
use App\Types\TypeNote;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class APOutboxController extends Controller
{
    public function outbox (User $user, Request $request)
    {
        // TODO: check we are logged in and we are the logged in user
        switch ($request->get ("type"))
        {
            case "UpdateProfile":
                return $this->handle_update_profile ($user);
                break;

            case "Follow":
                return $this->handle_follow ($user, $request->get ("object"));
                break;

            case "Unfollow":
                return $this->handle_unfollow ($user, $request->get ("object"));
                break;

            case "Post":
                return $this->handle_post ($user, $request);
                break;

            default:
                Log::info ("APOutboxController@index");
                Log::info (json_encode (request ()->all ()));
                break;
        }
    }

    public function handle_update_profile (User $user)
    {
        $actor = $user->actor ()->first ();
        $actor_response = TypeActor::build_response ($actor);

        $update_activity = TypeActivity::craft_update ($actor, $actor_response);
        $instances = Instance::all ();
        foreach ($instances as $instance)
        {
            $response = TypeActivity::post_activity ($update_activity, $actor, $instance->inbox);
            if ($response->getStatusCode () < 200 || $response->getStatusCode () >= 300)
                continue;
        }
        return response ()->json ("success", 200);
    }

    public function handle_follow (User $user, string $object)
    {
        $object_actor = Actor::where ("actor_id", $object)->first ();
        if (!$object_actor)
            return response ()->json ([ "error" => "object not found" ], 404);

        if ($user->actor ()->first ()->actor_id == $object_actor->actor_id)
            return response ()->json ([ "error" => "cannot follow self" ], 400);

        // check we are not following already
        $following_activity = Activity::where ("actor", $user->actor ()->first ()->actor_id)
            ->where ("object", '"' . str_replace ("/", "\/", $object_actor->actor_id) . '"')
            ->where ("type", "Follow")
            ->first ();
        if ($following_activity)
            return response ()->json ([ "error" => "already following" ], 400);

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

    public function handle_post (User $user, $request)
    {
        $actor = $user->actor ()->first ();
        $note = TypeNote::craft_from_outbox ($actor, $request);

        if (isset ($request ["attachments"]))
        {
            foreach ($request ["attachments"] as $attachment)
            {
                $attachment_note = NoteAttachment::create ([
                    "note_id" => $note->id,
                    "url" => $attachment
                ]);
            }
        }

        $create_activity = TypeActivity::craft_create ($actor, $note);

        $note->activity_id = $create_activity->id;
        $note->save ();

        $instances = Instance::all ();

        foreach ($instances as $instance)
        {
            $response = TypeActivity::post_activity ($create_activity, $actor, $instance->inbox);
            if ($response->getStatusCode () < 200 || $response->getStatusCode () >= 300)
                continue;
        }
    }
}
