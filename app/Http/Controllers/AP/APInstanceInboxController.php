<?php

namespace App\Http\Controllers\AP;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Models\Actor;
use App\Models\Activity;

use App\Types\TypeActor;
use App\Types\TypeActivity;

use App\Http\Controllers\Controller;

class APInstanceInboxController extends Controller
{
    public function inbox ()
    {
        $activity = request ()->all ();
        $activity_type = $activity['type'];

        switch ($activity_type)
        {
            case "Update":
                return $this->handle_update ($activity);
                break;

            default:
                Log::info ("APInstanceInboxController:inbox");
                Log::info (json_encode (request ()->all ()));
                break;
        }

        return response ()->json (["status" => "ok"]);
    }

    public function handle_update ($activity)
    {
        if (TypeActivity::activity_exists ($activity ["id"]))
            return response ()->json (["status" => "ok"]);

        $activity ["activity_id"] = $activity ["id"];
        $new_activity = Activity::create ($activity);

        $object = $activity ["object"];

        switch ($object ["type"])
        {
            case "Person":
                return $this->handle_update_person ($object);
                break;
        }

        return response ()->json (["status" => "ok"]);
    }

    public function handle_update_person ($person)
    {
        $actor = Actor::where ("actor_id", $person ["id"])->first ();
        if (!$actor)
            $actor = TypeActor::create_from_request ($person);

        TypeActor::update_from_request ($actor, $person);
        $actor->save ();

        return response ()->json (["status" => "ok"]);
    }
}
