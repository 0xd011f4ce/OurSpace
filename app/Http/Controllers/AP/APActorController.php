<?php

namespace App\Http\Controllers\AP;

use App\Models\User;
use App\Models\Actor;
use App\Models\Activity;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Types\TypeOrderedCollection;

class APActorController extends Controller
{
    public function user (User $user)
    {
        $actor = $user->actor ()->get ();
        $response = Actor::build_response ($actor->first ());
        return response ()->json ($response)->header ("Content-Type", "application/activity+json");
    }

    public function followers (User $user)
    {
        // TODO: Rewrite this using the follow model
        $followers = Activity::where ("type", "Follow")->where ("object", $user->actor->actor_id);
        $ordered_collection = new TypeOrderedCollection ();
        $ordered_collection->collection = $followers->get ()->pluck ("actor")->toArray ();
        $ordered_collection->url = route ("ap.followers", $user->name);
        $ordered_collection->page_size = 10;

        if (request ()->has ("page")) {
            $page = request ()->input ("page");
            return response ()->json ($ordered_collection->build_response_for_page ($page))->header ("Content-Type", "application/activity+json");
        }

        return response ()->json ($ordered_collection->build_response_main ())->header ("Content-Type", "application/activity+json");
    }

    public function following (User $user)
    {
        // TODO: Rewrite this using the follow model
        $following = Activity::where ("type", "Follow")->where ("actor", $user->actor->actor_id);
        $ordered_collection = new TypeOrderedCollection ();
        $ordered_collection->collection = $following->get ()->pluck ("object")->toArray ();
        $ordered_collection->url = route ("ap.following", $user->name);
        $ordered_collection->page_size = 10;

        if (request ()->has ("page")) {
            $page = request ()->input ("page");
            return response ()->json ($ordered_collection->build_response_for_page ($page))->header ("Content-Type", "application/activity+json");
        }

        return response ()->json ($ordered_collection->build_response_main ())->header ("Content-Type", "application/activity+json");
    }
}
