<?php

namespace App\Http\Controllers;

use App\Types\TypeActor;

use App\Models\User;
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
}
