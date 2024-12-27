<?php

namespace App\Http\Controllers\AP;

use App\Models\User;
use App\Models\Actor;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class APOutboxController extends Controller
{
    public function outbox (User $user)
    {
        Log::info ("APOutboxController@index");
    }
}
