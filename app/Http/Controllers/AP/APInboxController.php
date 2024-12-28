<?php

namespace App\Http\Controllers\AP;

use App\Models\User;
use App\Models\Actor;
use App\Models\Activity;

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

        switch ($type) {
            case "Follow":
                $this->handle_follow ($user, $request->all ());
                break;

            case "Undo":
                $this->handle_undo ($user, $request->all ());
                break;
        }

        Log::info ("APInboxController@index");
        Log::info (json_encode ($request->all ()));
    }

    private function handle_follow (User $user, $activity)
    {
        if (TypeActivity::activity_exists ($activity ["id"]))
            return response ()->json (["error" => "Activity already exists",], 409);

        $actor = TypeActor::actor_exists_or_obtain ($activity ["actor"]);

        $target = TypeActor::actor_get_local ($activity ["object"]);
        if (!$target || !$target->user)
            return response ()->json (["error" => "Target not found",], 404);

        $activity ["activity_id"] = $activity ["id"];

        // there's no follows model, it'll be handled with the activity model
        $act = Activity::create ($activity);

        // TODO: Users should be able to manually check this
        $accept_activity = TypeActivity::craft_accept ($act);
        $response = TypeActivity::post_activity ($accept_activity, $target, $actor);
        if (!$response)
        {
            return response ()->json ([
                "error" => "Error posting activity",
            ], 500);
        }

        $target->user->friends += 1;
        $target->user->save ();
    }

    public function handle_undo (User $user, $activity)
    {
        if (TypeActivity::activity_exists ($activity ["id"]))
            return response ()->json (["error" => "Activity already exists",], 409);

        $actor = TypeActor::actor_exists_or_obtain ($activity ["actor"]);

        $child_activity = $activity ["object"];
        if (!TypeActivity::activity_exists ($child_activity ["id"]))
            return response ()->json (["error" => "Child activity not found",], 404);

        $child_activity = Activity::where ("activity_id", $child_activity ["id"])->first ();
        switch ($child_activity->type)
        {
            case "Follow":
                // TODO: Move this to its own function
                // TODO: Should the accept activity be deleted?
                $followed_user = TypeActor::actor_get_local ($child_activity ["object"]);
                if (!$followed_user || !$followed_user->user)
                    return response ()->json (["error" => "Target not found",], 404);

                $followed_user->user->friends -= 1;
                $followed_user->user->save ();

                $child_activity->delete ();
                break;

            default:
                Log::info ("Unknown activity type to Undo: " . $child_activity ["type"]);
                break;
        }

        // TODO: Should Undo create a new activity model?
        return response ()->json (["error" => "Not implemented",], 501);
    }
}
