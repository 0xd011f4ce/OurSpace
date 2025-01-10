<?php

namespace App\Http\Controllers\AP;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Models\Actor;
use App\Models\Activity;
use App\Models\Announcement;
use App\Models\ProfilePin;

use App\Types\TypeActor;
use App\Types\TypeActivity;
use App\Types\TypeNote;

use App\Actions\ActionsActivity;

use App\Http\Controllers\Controller;

use App\Notifications\UserNotification;

class APInstanceInboxController extends Controller
{
    public function inbox ()
    {
        $activity = request ()->all ();
        $activity_type = $activity['type'];

        Log::info ("APInstanceInboxController:inbox");
        Log::info (json_encode ($activity));

        switch ($activity_type)
        {
            case "Announce":
                return $this->handle_announce ($activity);
                break;

            case "Add":
                return $this->handle_add ($activity);
                break;

            case "Remove":
                return $this->handle_remove ($activity);
                break;

            case "Undo":
                return $this->handle_undo ($activity);
                break;

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

    public function handle_announce ($activity)
    {
        // we suppose an announce is always a note
        $note_exists = TypeNote::note_exists ($activity ["object"]);
        if (!$note_exists)
        {
            $note_exists = TypeNote::obtain_external ($activity ["object"]);
            if (!$note_exists)
                return response ()->json (["status" => "error"]);
        }

        $announcement_exists = TypeActivity::activity_exists ($activity ["id"]);
        if ($announcement_exists)
            return response ()->json (["status" => "ok"]);

        $announcement_actor = TypeActor::actor_exists_or_obtain ($activity ["actor"]);
        if (!$announcement_actor)
            return response ()->json (["status" => "error"]);

        $activity["activity_id"] = $activity["id"];
        $ann_act = Activity::create ($activity);
        $announcement = Announcement::create ([
            "activity_id" => $ann_act->id,
            "actor_id" => $announcement_actor->id,
            "note_id" => $note_exists->id
        ]);

        $note_actor = $note_exists->get_actor ()->first ();
        if ($note_actor->user)
        {
            $note_actor->user->notify (new UserNotification("Boost", $announcement_actor, $note_exists));
        }

        return response ()->json (["status" => "ok"]);
    }

    public function handle_add ($activity)
    {
        $actor = TypeActor::actor_exists_or_obtain ($activity ["actor"]);
        if (!$actor)
            return response ()->json (["status" => "error"]);

        if ($activity["target"] != $actor->featured)
            // For now we only support adding notes to the featured actor
            return response ()->json (["error" => "not implemented"], 501);

        $note = TypeNote::note_exists ($activity ["object"]);
        if (!$note)
            $note = TypeNote::obtain_external ($activity ["object"]);

        $pin_exists = ProfilePin::where ("actor_id", $actor->id)->where ("note_id", $note->id)->first ();
        if ($pin_exists)
            return response ()->json (["status" => "ok"]);

        ProfilePin::create ([
            "actor_id" => $actor->id,
            "note_id" => $note->id
        ]);

        return response ()->json (["status" => "ok"]);
    }

    public function handle_remove ($activity)
    {
        $actor = TypeActor::actor_exists_or_obtain ($activity ["actor"]);
        if (!$actor)
            return response ()->json (["status" => "error"]);

        if ($activity ["target"] != $actor->featured)
            // For now we only support removing notes from the featured actor
            return response ()->json (["error" => "not implemented"], 501);

        $note = TypeNote::note_exists ($activity ["object"]);
        $pin_exists = ProfilePin::where ("actor_id", $actor->id)->where ("note_id", $note->id)->first ();
        if (!$pin_exists)
            return response ()->json (["status" => "ok"]);

        $pin_exists->delete ();
    }

    public function handle_undo ($activity)
    {
        return response ()->json (ActionsActivity::activity_undo($activity));
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
        if (!TypeActivity::activity_exists ($activity ["id"]))
        {
            $activity ["activity_id"] = $activity ["id"];
            $new_activity = Activity::create ($activity);
        }

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
