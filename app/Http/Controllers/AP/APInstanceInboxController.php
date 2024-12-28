<?php

namespace App\Http\Controllers\AP;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Http\Controllers\Controller;

class APInstanceInboxController extends Controller
{
    public function inbox ()
    {
        Log::info ("APInstanceInboxController:inbox");
        Log::info (json_encode (request ()->all ()));
    }
}
