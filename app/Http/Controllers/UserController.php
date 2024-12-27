<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Actor;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function login ()
    {
        return view ("auth.login");
    }

    public function signup ()
    {
        return view ("auth.signup");
    }

    public function do_signup (Request $request)
    {
        $incoming_fields = $request->validate ([
            "name" => "required|alpha_dash",
            "email" => "required|email|unique:users",
            "password" => "required|confirmed"
        ]);

        $user = User::create ($incoming_fields);
        $actor = new Actor ();
        $actor->create_from_user ($user);
        auth ()->login ($user);

        return redirect ()->route ("home")->with ("success", "You have successfuly signed up!");
    }

    public function do_login (Request $request)
    {
        $incoming_fields = $request->validate ([
            "email" => "required|email",
            "password" => "required"
        ]);

        if (auth ()->attempt (["email" => $incoming_fields["email"], "password" => $incoming_fields["password"]], isset ($incoming_fields["remember"]))) {
            $request->session ()->regenerate ();
            return redirect ()->route ("home")->with ("success", "You have successfuly logged in!");
        }
        return redirect ()->route ("login")->with ("error", "Invalid credentials!");
    }

    public function logout ()
    {
        auth ()->logout ();

        return redirect ()->route ("login")->with ("success", "You have successfuly logged out!");
    }
}
