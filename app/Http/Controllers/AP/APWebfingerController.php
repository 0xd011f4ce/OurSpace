<?php

namespace App\Http\Controllers\AP;

use App\Models\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class APWebfingerController extends Controller
{
    public function webfinger ()
    {
        $resource = request ()->input ("resource");
        if (!isset ($resource)) {
            return response ()->json ([ "error" => "missing resource parameter" ], 400);
        }

        $host = parse_url ($resource, PHP_URL_HOST);
        $user = explode (":", $resource);
        if (count ($user) != 2) {
            return response ()->json ([ "error" => "invalid resource parameter" ], 400);
        }

        $user = $user[1];
        $user = explode ("@", $user);
        if (count ($user) != 2) {
            return response ()->json ([ "error" => "invalid resource parameter" ], 400);
        }

        $user = $user[0];
        $actual_user = User::where ("name", $user)->first ();
        $webfinger = [
            "subject" => $resource,
            "links" => [
                [
                    "rel" => "self",
                    "type" => "application/activity+json",
                    "href" => $actual_user->actor ()->first ()->actor_id
                ]
            ]
        ];
        return response ()->json ($webfinger)->header ("Content-Type", "application/jrd+json");
    }
}
