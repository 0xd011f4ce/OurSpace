<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function home ()
    {
        $latest_users = User::latest ()->take (4)->get ();

        return view ("home", compact ("latest_users"));
    }
}
