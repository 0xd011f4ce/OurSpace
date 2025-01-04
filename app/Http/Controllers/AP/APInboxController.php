<?php

namespace App\Http\Controllers\AP;

use App\Models\User;
use App\Models\Actor;
use App\Models\Activity;
use App\Models\Follow;

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
        }
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
        if (TypeActivity::activity_exists ($activity ["id"]))
            return response ()->json (["error" => "Activity already exists",], 409);

        $actor = TypeActor::actor_exists_or_obtain ($activity ["actor"]);

        $child_activity = $activity ["object"];
        $child_activity_id = "";

        if (is_array ($child_activity))
            $child_activity_id = $child_activity ["id"];
        else
            $child_activity_id = $child_activity;

        if (!TypeActivity::activity_exists ($child_activity_id))
            return response ()->json (["error" => "Child activity not found",], 404);

        $child_activity = Activity::where ("activity_id", $child_activity_id)->first ();
        $child_activity->delete ();

        // TODO: Should Undo create a new activity in database?
        return response ()->json (["success" => "Activity undone",], 200);
    }
}
