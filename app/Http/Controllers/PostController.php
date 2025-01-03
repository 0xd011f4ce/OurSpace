<?php

namespace App\Http\Controllers;

use App\Models\Note;

use GuzzleHttp\Client;
use App\Actions\ActionsPost;

use Illuminate\Http\Request;

use App\Models\NoteAttachment;
use Illuminate\Support\Facades\Log;

class PostController extends Controller
{
    public function show (Note $note)
    {
        $actor = $note->get_actor ()->first ();

        return view ("posts.show", compact ("note", "actor"));
    }

    public function edit (Note $note)
    {
        $actor = $note->get_actor ()->first ();
        $note_user = $actor->user ()->first ();
        if (!auth()->user ()->is ($note_user)) {
            return back ()->with ("error", "You are not allowed to edit this post.");
        }

        return view ("posts.edit", compact ("note", "actor"));
    }

    public function update (Note $note, Request $request)
    {
        $actor = auth ()->user ()->actor ()->first ();
        $note_user = $actor->user ()->first ();
        if (!auth ()->user ()->is ($note_user)) {
            return back ()->with ("error", "You are not allowed to edit this post.");
        }

        $incoming_fields = $request->validate ([
            "summary" => "nullable|string",
            "content" => "required|string",
            "files" => "nullable|array",
            "files.*" => "image"
        ]);

        $processed = ActionsPost::process_content_and_attachments ($request);

        try {
            $client = new Client ();
            $client->request ("POST", $note->get_actor ()->first ()->outbox, [
                "json" => [
                    "type" => "UpdateNote",
                    "note" => $note->id,
                    "summary" => $processed["summary"],
                    "content" => $processed["content"],
                    "attachments" => $processed["attachments"]
                ]
            ]);

            return redirect ()->route ("posts.show", $note)->with ("success", "Post updated successfully.");
        } catch (\Exception $e) {
            return back ()->with ("error", "An error occurred while updating the post.");

            Log::error ("An error occurred while updating the post.");
            Log::error ($e->getMessage ());
        }
    }
}
