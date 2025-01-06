<?php

namespace App\Http\Controllers\AP;

use App\Actions\ActionsActivity;
use App\Models\User;
use App\Models\Actor;
use App\Models\Activity;
use App\Models\Follow;
use App\Models\Note;
use App\Models\Like;

use App\Types\TypeActor;
use App\Types\TypeActivity;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class APInboxController extends Controller
{
    public function inbox (User $user)
    {
        $request = request ();
        $type = $request->get ("type");

        Log::info ("APInboxController@index");
        Log::info (json_encode ($request->all ()));

        switch ($type) {
            case "Follow":
                $this->handle_follow ($user, $request->all ());
                break;

            case "Undo":
                $this->handle_undo ($user, $request->all ());
                break;

            case "Like":
                $this->handle_like ($user, $request->all ());
                break;

            default:
                Log::info ("APInboxController@index");
                Log::info ("Unknown type: " . $type);
                break;
        }
    }

    private function handle_follow (User $user, $activity)
    {
        if (TypeActivity::activity_exists ($activity["id"]))
            return response ()->json (["error" => "Activity already exists",], 409);

        $actor = TypeActor::actor_exists_or_obtain ($activity ["actor"]);

        $target = TypeActor::actor_get_local ($activity ["object"]);
        if (!$target || !$target->user)
            return response ()->json (["error" => "Target not found",], 404);

        // check follow doesn't exist
        $follow_exists = Follow::where ("actor", $actor->id)
            ->where ("object", $target->id)
            ->first ();
        if ($follow_exists)
            return response ()->json (["error" => "Follow already exists",], 409);

        $activity ["activity_id"] = $activity ["id"];

        $act = Activity::create ($activity);

        $follow = Follow::create ([
            "activity_id" => $act->id,
            "actor" => $actor->id,
            "object" => $target->id,
        ]);

        // TODO: Users should be able to manually check this
        $accept_activity = TypeActivity::craft_accept ($act);
        $response = TypeActivity::post_activity ($accept_activity, $target, $actor);
        if (!$response)
        {
            return response ()->json ([
                "error" => "Error posting activity",
            ], 500);
        }
    }

    public function handle_undo (User $user, $activity)
    {
        return response ()->json (ActionsActivity::activity_undo ($activity));
    }

    public function handle_like (User $user, $activity)
    {
        $actor = TypeActor::actor_exists_or_obtain ($activity ["actor"]);
        $note_id = $activity ["object"];
        $note = Note::where ("note_id", $note_id)->first ();
        if (!$note)
        {
            Log::info ("Note not found: " . $note_id);
            return response ()->json (["error" => "Note not found",], 404);
        }

        // check like doesn't already exist
        $like_exists = $actor->liked_note ($note);
        if ($like_exists)
            return response ()->json (["error" => "Like already exists",], 409);

        $activity ["activity_id"] = $activity ["id"];
        $activity_exists = TypeActivity::activity_exists ($activity ["id"]);
        if (!$activity_exists)
            $act = Activity::create ($activity);
        else
            $act = Activity::where ("activity_id", $activity ["id"])->first ();

        $like = Like::create ([
            "activity_id" => $act->id,
            "actor_id" => $actor->id,
            "note_id" => $note->id,
        ]);

        return response ()->json (["success" => "Like created",], 200);
    }
}
