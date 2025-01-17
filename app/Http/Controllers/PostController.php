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

        if (!$actor)
            return redirect ()->back ();

        if (!$note->can_view ())
            return redirect ()->back ()->with ("error", "You are not allowed to view this post.");
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

    public function like (Note $note)
    {
        if (!auth ()->check ())
            return back ()->with ("error", "You need to be logged in to like a post.");

        $user = auth ()->user ();
        $actor = $user->actor ()->first ();

        $response = ActionsPost::like_post ($actor, $note);

        return back ()->with ("success", "Post liked successfully.");
    }

    public function boost (Note $note)
    {
        if (!auth ()->check ())
            return back ()->with ("error", "You need to be logged in to boost a post.");

        $user = auth ()->user ();
        $actor = $user->actor ()->first ();

        $response = ActionsPost::boost_post ($actor, $note);

        return back ()->with ("success", "Post boosted successfully.");
    }

    public function pin (Note $note)
    {
        if (!auth ()->check ())
            return back ()->with ("error", "You need to be logged in to pin a post.");

        $actor = $note->get_actor ()->first ();

        $response = ActionsPost::pin_post ($actor, $note);

        return back ()->with ("success", "Post pinned successfully.");
    }

    public function delete (Note $note)
    {
        $actor = auth ()->user ()->actor ()->first ();
        $note_user = $actor->user ()->first ();
        if (!auth ()->user ()->is ($note_user)) {
            return back ()->with ("error", "You are not allowed to delete this post.");
        }

        try {
            $client = new Client ();
            $client->request ("POST", $note->get_actor ()->first ()->outbox, [
                "json" => [
                    "type" => "DeleteNote",
                    "note" => $note->id
                ]
            ]);

            return redirect ()->route ("home")->with ("success", "Post deleted successfully.");
        } catch (\Exception $e) {
            return back ()->with ("error", "An error occurred while deleting the post.");

            Log::error ("An error occurred while deleting the post.");
            Log::error ($e->getMessage ());
        }
    }
}
