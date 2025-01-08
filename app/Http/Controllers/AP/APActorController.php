<?php

namespace App\Http\Controllers\AP;

use App\Models\User;
use App\Models\Actor;
use App\Models\Activity;
use App\Models\Follow;
use App\Models\Note;
use App\Models\ProfilePin;

use App\Types\TypeOrderedCollection;
use App\Types\TypeNote;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class APActorController extends Controller
{
    public function user (User $user)
    {
        if (str_contains (request ()->header ("Accept"), "text/html")) {
            return redirect (route ("users.show", ["user_name" => $user->name]));
        }

        $actor = $user->actor ()->get ();
        $response = Actor::build_response ($actor->first ());
        return response ()->json ($response)->header ("Content-Type", "application/activity+json");
    }

    public function followers (User $user)
    {
        $follower_ids = Follow::where ("object", $user->actor->id)->get ();
        $followers = Actor::whereIn ("id", $follower_ids->pluck ("actor")->toArray ());

        $ordered_collection = new TypeOrderedCollection ();
        $ordered_collection->collection = $followers->get ()->pluck ("actor_id")->toArray ();
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
        $following_ids = Follow::where ("actor", $user->actor->id)->get ();
        $following = Actor::whereIn ("id", $following_ids->pluck ("object")->toArray ());

        $ordered_collection = new TypeOrderedCollection ();
        $ordered_collection->collection = $following->get ()->pluck ("actor_id")->toArray ();
        $ordered_collection->url = route ("ap.following", $user->name);
        $ordered_collection->page_size = 10;

        if (request ()->has ("page")) {
            $page = request ()->input ("page");
            return response ()->json ($ordered_collection->build_response_for_page ($page))->header ("Content-Type", "application/activity+json");
        }

        return response ()->json ($ordered_collection->build_response_main ())->header ("Content-Type", "application/activity+json");
    }

    public function featured (User $user)
    {
        $featured_ids = ProfilePin::where ("actor_id", $user->actor->id)->pluck ("note_id")->toArray ();
        $notes = Note::whereIn ("id", $featured_ids)->get ();

        $collection = [];
        foreach ($notes as $note)
        {
            $collection[] = TypeNote::build_response ($note);
        }

        $ordered_collection = new TypeOrderedCollection ();
        $ordered_collection->collection = $collection;
        $ordered_collection->url = route ("ap.featured", $user->name);
        $ordered_collection->page_size = 10;

        if (request ()->has ("page")) {
            $page = request ()->input ("page");
            return response ()->json ($ordered_collection->build_response_for_page ($page))->header ("Content-Type", "application/activity+json");
        }

        return response ()->json ($ordered_collection->build_response_main ())->header ("Content-Type", "application/activity+json");
    }
}
