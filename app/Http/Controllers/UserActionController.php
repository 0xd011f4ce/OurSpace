<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Actions\ActionsFriends;
use App\Actions\ActionsPost;

class UserActionController extends Controller
{
    public function friend (Request $request)
    {
        $response = ActionsFriends::add_friend ($request->get ("object"));
        if (isset ($response ["error"]))
            return back ()->with ("error", $response ["error"]);

        return back ()->with ("success", $response ["success"]);
    }

    public function unfriend (Request $request)
    {
        $response = ActionsFriends::remove_friend ($request->get ("object"));
        if (isset ($response ["error"]))
            return back ()->with ("error", $response ["error"]);

        return back ()->with ("success", $response ["success"]);
    }

    public function post_new (Request $request)
    {
        $request->validate ([
            "summary" => "nullable|string",
            "content" => "required",
            "files.*" => "max:4096"
        ]);

        $response = ActionsPost::post_new ($request);
        if (isset ($response ["error"]))
            return back ()->with ("error", $response ["error"]);

        return back ()->with ("success", $response ["success"]);
    }
}
