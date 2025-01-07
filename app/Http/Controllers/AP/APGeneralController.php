<?php

namespace App\Http\Controllers\AP;

use App\Models\Note;

use App\Types\TypeNote;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class APGeneralController extends Controller
{
    public function note (Note $note)
    {
        if (str_contains (request ()->header ("Accept"), "text/html")) {
            return redirect (route ("posts.show", ["note" => $note]));
        }

        $response = TypeNote::build_response ($note);
        return response ()->json ($response)->header ("Content-Type", "application/activity+json");
    }
}
