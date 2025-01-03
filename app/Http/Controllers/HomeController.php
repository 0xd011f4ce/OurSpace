<?php

namespace App\Http\Controllers;

use App\Types\TypeActor;
use App\Actions\ActionsFriends;

use App\Models\User;
use App\Models\Actor;

use GuzzleHttp\Client;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function home ()
    {
        $latest_users = User::latest ()->take (4)->get ();

        return view ("home", compact ("latest_users"));
    }

    public function search ()
    {
        $query = request ()->get ("query");

        // check if the query is empty
        if (empty ($query)) {
            return redirect ()->route ("home");
        }

        // check if the search is a federated user
        $user_handle = array_slice (explode ("@", $query), 1);
        if (count ($user_handle) > 1) {
            $username = $user_handle[0];
            $domain = $user_handle[1];

            $actor = TypeActor::actor_exists_or_obtain_from_handle ($username, $domain);
            if (!$actor)
                return redirect ()->route ("home");

            return redirect ()->route ("users.show", "@$actor->preferredUsername@$domain");
        }
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
