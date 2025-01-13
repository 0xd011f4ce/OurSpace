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
    public function user ($name)
    {
        $actor = Actor::where ("preferredUsername", $name)->where ("user_id", "!=", null)->first ();
        if (!$actor)
            return response ()->json (["error" => "Actor not found"], 404)->header ("Content-Type", "application/activity+json");

        if (str_contains (request ()->header ("Accept"), "text/html")) {
            if ($actor->blog_id) {
                return redirect (route ("blogs.show", ["blog" => $actor->preferredUsername]));
            }

            return redirect (route ("users.show", ["user_name" => $actor->preferredUsername]));
        }

        $response = Actor::build_response ($actor);
        return response ()->json ($response)->header ("Content-Type", "application/activity+json");
    }

    public function followers ($name)
    {
        $actor = Actor::where ("preferredUsername", $name)->where ("user_id", "!=", null)->first ();
        if (!$actor)
            return response ()->json (["error" => "Actor not found"], 404)->header ("Content-Type", "application/activity+json");

        $follower_ids = Follow::where ("object", $actor->id)->get ();
        $followers = Actor::whereIn ("id", $follower_ids->pluck ("actor")->toArray ());

        $ordered_collection = new TypeOrderedCollection ();
        $ordered_collection->collection = $followers->get ()->pluck ("actor_id")->toArray ();
        $ordered_collection->url = route ("ap.followers", $actor->name);
        $ordered_collection->page_size = 10;

        if (request ()->has ("page")) {
            $page = request ()->input ("page");
            return response ()->json ($ordered_collection->build_response_for_page ($page))->header ("Content-Type", "application/activity+json");
        }

        return response ()->json ($ordered_collection->build_response_main ())->header ("Content-Type", "application/activity+json");
    }

    public function following ($name)
    {
        $actor = Actor::where ("preferredUsername", $name)->where ("user_id", "!=", null)->first ();
        if (!$actor)
            return response ()->json (["error" => "Actor not found"], 404)->header ("Content-Type", "application/activity+json");

        $following_ids = Follow::where ("actor", $actor->id)->get ();
        $following = Actor::whereIn ("id", $following_ids->pluck ("object")->toArray ());

        $ordered_collection = new TypeOrderedCollection ();
        $ordered_collection->collection = $following->get ()->pluck ("actor_id")->toArray ();
        $ordered_collection->url = route ("ap.following", $actor->name);
        $ordered_collection->page_size = 10;

        if (request ()->has ("page")) {
            $page = request ()->input ("page");
            return response ()->json ($ordered_collection->build_response_for_page ($page))->header ("Content-Type", "application/activity+json");
        }

        return response ()->json ($ordered_collection->build_response_main ())->header ("Content-Type", "application/activity+json");
    }

    public function featured ($name)
    {
        $actor = Actor::where ("preferredUsername", $name)->where ("user_id", "!=", null)->first ();
        if (!$actor)
            return response ()->json (["error" => "Actor not found"], 404)->header ("Content-Type", "application/activity+json");

        $featured_ids = ProfilePin::where ("actor_id", $actor->id)->pluck ("note_id")->toArray ();
        $notes = Note::whereIn ("id", $featured_ids)->get ();

        $collection = [];
        foreach ($notes as $note)
        {
            $collection[] = TypeNote::build_response ($note);
        }

        $ordered_collection = new TypeOrderedCollection ();
        $ordered_collection->collection = $collection;
        $ordered_collection->url = route ("ap.featured", $actor->preferredUsername);
        $ordered_collection->page_size = 10;

        if (request ()->has ("page")) {
            $page = request ()->input ("page");
            return response ()->json ($ordered_collection->build_response_for_page ($page))->header ("Content-Type", "application/activity+json");
        }

        return response ()->json ($ordered_collection->build_response_main ())->header ("Content-Type", "application/activity+json");
    }
}
