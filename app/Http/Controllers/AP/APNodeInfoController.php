<?php

namespace App\Http\Controllers\AP;

use App\Models\User;
use App\Models\Note;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class APNodeInfoController extends Controller
{
    public function wk_nodeinfo ()
    {
        $response = [
            "links" => [
                "rel" => "self",
                "type" => "http://nodeinfo.diaspora.software/ns/schema/2.1",
                "href" => env ("APP_URL") . "/.well-known/nodeinfo/2.1"
            ]
        ];

        return response ()->json ($response);
    }

    public function nodeinfo ()
    {
        $total_users = User::count ();
        $active_month_users = User::where ("last_seen_at", ">=", now ()->subMonth ())->count ();
        $active_half_year_users = User::where ("last_seen_at", ">=", now ()->subMonths (6))->count ();
        $local_posts = Note::where ("private_id", "!=", null)->count ();

        $response = [
            "version" => "2.1",
            "software" => [
                "name" => "OurSpace",
                "version" => env ("APP_VERSION"),
                "repository" => "https://github.com/0xd011f4ce/OurSpace"
            ],
            "protocols" => [
                "activitypub"
            ],
            "services" => [
                "inbound",
                "outbound"
            ],
            "openRegistrations" => true,
            "usage" => [
                "users" => [
                    "total" => $total_users,
                    "activeMonth" => $active_month_users,
                    "activeHalfYear" => $active_half_year_users
                ],
                "localPosts" => $local_posts
            ],
            "metadata" => [
                "nodeName" => env ("APP_NAME"),
                "nodeDescription" => env ("APP_DESCRIPTION"),
                "spdx" => "GPL-3.0",
            ]
        ];

        return response ()->json ($response);
    }
}
