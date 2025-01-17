<?php

namespace App\Http\Controllers;

use App\Types\TypeActor;
use App\Actions\ActionsFriends;

use App\Models\User;
use App\Models\Actor;
use App\Models\Note;
use App\Models\Hashtag;
use App\Models\BlogCategory;

use App\Helpers\PaginationHelper;

use GuzzleHttp\Client;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function home ()
    {
        $latest_users = User::latest ()->take (4)->get ();

        return view ("home", compact ("latest_users"));
    }

    public function browse ()
    {
        $users = [];
        $notes = [];
        if (request ()->get ("users") == "all")
        {
            $users = Actor::latest ()->take (10)->get ();
        }
        else
        {
            $users = User::latest ()->take (10)->get ();
        }

        $popular_hashtags = Hashtag::withCount ("get_notes")->orderBy ("get_notes_count", "desc")->take (16)->get ()->shuffle ();

        if (request ()->get ("posts") == "latest")
        {
            $notes = Note::latest ();
        }
        else
        {
            $notes = Note::withCount ([ "get_likes" => function ($query) {
                $query->where ("created_at", ">=", now ()->subDay ());
            }])->where ("in_reply_to", null)->orderBy ("get_likes_count", "desc");
        }

        $notes = $notes->paginate (10);

        return view ("browse", compact ("users", "popular_hashtags", "notes"));
    }

    public function tag ($tag)
    {
        $tag_name = "#" . $tag;
        $hashtag = Hashtag::where ("name", $tag_name)->first ();
        if (!$hashtag)
            return redirect ()->route ("browse");

        $posts = $hashtag->get_notes ()->paginate (20);

        return view ("posts.tag", compact ("hashtag", "posts"));
    }

    public function search ()
    {
        $query = request ()->get ("query");

        // check if the query is empty
        if ($query == null) {
            return view ("search");
        }

        // check if the search is a federated user
        $user_handle = array_slice (explode ("@", $query), 1);
        $at_count = count ($user_handle);
        if ($at_count >= 1) {
            switch ($at_count)
            {
                case 1:
                    $user = User::where ("name", $user_handle[0])->first ();
                    if (!$user)
                        break;

                    return redirect ()->route ("users.show", $user->name);
                    break;

                case 2:
                    $username = $user_handle[0];
                    $domain = $user_handle[1];

                    $actor = TypeActor::actor_exists_or_obtain_from_handle ($username, $domain);
                    if (!$actor)
                        break;

                    return redirect ()->route ("users.show", "@$username@$domain");
                    break;
            }
        }

        $local_users = User::where ("name", "like", "%$query%")->orderBy ("created_at", "desc")->get ();
        $actors = Actor::where ("name", "like", "%$query%")->orWhere ("preferredUsername", "like", "%$query%")->orWhere ("summary", "like", "%$query%")->orderBy ("created_at", "desc")->get ();

        $users = $local_users->merge ($actors)->take (10);
        $hashtags = Hashtag::withCount ("get_notes")->where ("name", "like", "%$query%")->orderBy ("get_notes_count", "desc")->take (16)->get ()->shuffle ();
        $posts = Note::where ("content", "like", "%$query%")->orderBy ("created_at", "desc")->paginate (10);

        return view ("search", compact ("users", "hashtags", "posts"));
    }

    public function requests ()
    {
        $user = auth ()->user ();
        $received_requests = [];
        $sent_requests = [];

        foreach ($user->received_requests () as $request)
        {
            $actor = Actor::where ("actor_id", $request)->first ();
            if (!$actor)
                continue;

            $received_requests[] = $actor;
        }

        foreach ($user->sent_requests () as $request)
        {
            $actor = Actor::where ("actor_id", $request)->first ();
            if (!$actor)
                continue;

            $sent_requests[] = $actor;
        }

        $received_requests = PaginationHelper::paginate (collect ($received_requests), 20, "received");
        $sent_requests = PaginationHelper::paginate (collect ($sent_requests), 20, "sent");

        return view ("users.requests", compact ("user", "received_requests", "sent_requests"));
    }

    public function requests_accept (Request $request)
    {
        $user = auth ()->user ();

        if (isset ($request->accept))
        {
            // accept a single request
            $target = $request->accept;
            $action = ActionsFriends::add_friend ($target);
            if (isset ($action ["error"]))
            {
                return back ()->with ("error", $action ["error"]);
            }

            return back ()->with ("success", $action ["success"]);
        }
    }
}
