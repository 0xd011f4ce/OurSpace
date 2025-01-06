<?php

namespace App\Actions;

use App\Models\Activity;

use App\Types\TypeActor;
use App\Types\TypeActivity;

class ActionsActivity
{
    public static function activity_undo ($activity)
    {
        $actor = TypeActor::actor_exists_or_obtain ($activity ["actor"]);

        $child_activity = $activity ["object"];
        $child_activity_id = "";

        if (is_array ($child_activity))
            $child_activity_id = $child_activity ["id"];
        else
            $child_activity_id = $child_activity;

        if (!TypeActivity::activity_exists ($child_activity_id))
            return ["error" => "Activity not found",];

        $child_activity = Activity::where ("activity_id", $child_activity_id)->first ();
        $child_activity->delete ();

        // TODO: Should Undo create a new activity in database?
        return ["success" => "Activity undone",];
    }
}
