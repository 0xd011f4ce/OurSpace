<?php

namespace App\Http\Controllers;

use App\Models\Note;

use Illuminate\Http\Request;

class PostController extends Controller
{
    public function show (Note $note)
    {
        $actor = $note->get_actor ()->first ();

        return view ("posts.show", compact ("note", "actor"));
    }
}
