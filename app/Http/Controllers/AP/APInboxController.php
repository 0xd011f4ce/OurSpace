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

use App\Events\NoteLikedEvent;

use App\Events\AP\ActivityUndoEvent;
use App\Events\AP\ActivityFollowEvent;
use App\Events\AP\ActivityLikeEvent;

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
        ActivityFollowEvent::dispatch ($activity);
    }

    public function handle_undo (User $user, $activity)
    {
        ActivityUndoEvent::dispatch ($activity);
    }

    public function handle_like (User $user, $activity)
    {
        ActivityLikeEvent::dispatch ($activity);
    }
}
