<?php

namespace App\Http\Controllers\AP;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Models\Actor;
use App\Models\Activity;

use App\Types\TypeActor;
use App\Types\TypeActivity;
use App\Types\TypeNote;

use App\Http\Controllers\Controller;

class APInstanceInboxController extends Controller
{
    public function inbox ()
    {
        $activity = request ()->all ();
        $activity_type = $activity['type'];

        Log::info ("APInstanceInboxController:inbox");
        Log::info ($activity);

        switch ($activity_type)
        {
            case "Create":
                return $this->handle_create ($activity);
                break;

            case "Delete":
                return $this->handle_delete ($activity);
                break;

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

    public function handle_create ($activity)
    {
        if (TypeActivity::activity_exists ($activity ["id"]))
            return response ()->json (["status" => "ok"]);

        $activity ["activity_id"] = $activity ["id"];
        $new_activity = Activity::create ($activity);

        $object = $activity ["object"];

        switch ($object ["type"])
        {
            case "Note":
                return $this->handle_create_note ($object, $new_activity);
                break;
        }

        return response ()->json (["status" => "ok"]);
    }

    public function handle_delete ($activity)
    {
        if (!is_array ($activity ["object"]))
            return response ()->json (["error" => "not implemented"]);

        // we suppose that we are deleting a note
        $note = TypeNote::note_exists ($activity ["object"]["id"]);
        if (!$note)
            return response ()->json (["status" => "ok"]);

        $activity = $note->get_activity ();
        $activity->delete ();

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

            case "Note":
                return $this->handle_update_note ($object);
                break;

            default:
                Log::info ("APInstanceInboxController:handle_update");
                Log::info (json_encode ($activity));
                break;
        }

        return response ()->json (["status" => "ok"]);
    }

    // create related functions
    public function handle_create_note ($note, Activity $activity)
    {
        $exists = TypeNote::note_exists ($note ["id"]);
        if ($exists)
            return response ()->json (["status" => "ok"]);

        $actor = TypeActor::actor_exists_or_obtain ($activity ["actor"]);
        if (!$actor)
            return response ()->json (["status" => "error"]);

        $created_note = TypeNote::create_from_request ($note, $activity, $actor);
        return response ()->json (["status" => "ok"]);
    }

    // update related functions
    public function handle_update_person ($person)
    {
        $actor = TypeActor::actor_exists ($person ["id"]);
        if (!$actor)
            $actor = TypeActor::create_from_request ($person);

        TypeActor::update_from_request ($actor, $person);
        $actor->save ();

        return response ()->json (["status" => "ok"]);
    }

    public function handle_update_note ($object)
    {
        $note = TypeNote::note_exists ($object ["id"]);
        if (!$note)
            return response ()->json (["status" => "ok"]);

        TypeNote::update_from_request ($note, $object, $note->get_activity ()->first (), $note->get_actor ()->first ());
        $note->save ();

        return response ()->json (["status" => "ok"]);
    }
}
