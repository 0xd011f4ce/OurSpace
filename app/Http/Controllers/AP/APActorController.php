<?php

namespace App\Http\Controllers\AP;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use App\Models\Actor;

class APActorController extends Controller
{
    public function user (User $user)
    {
        $actor = $user->actor ()->get ();
        $response = Actor::build_response ($actor->first ());
        return response ()->json ($response)->header ("Content-Type", "application/activity+json");
    }
}
