<?php

namespace App\Types;

use App\Models\Note;
use App\Models\Actor;
use App\Models\Activity;
use App\Models\NoteAttachment;

use Illuminate\Support\Facades\Log;

class TypeNote
{
    public static function update_from_request (Note $note, $request, Activity $activity, Actor $actor)
    {
        $note->activity_id = $activity->id;
        $note->actor_id = $actor->id;

        $note->note_id = $request["id"] ?? null;
        $note->in_reply_to = $request["inReplyTo"] ?? null;
        $note->summary = $request["summary"] ?? null;
        $note->url = $request["url"] ?? null;
        $note->attributedTo = $request["attributedTo"] ?? null;
        $note->content = $request["content"] ?? null;
        $note->tag = $request["tag"] ?? null;

        if ($request ["attachment"])
        {
            foreach ($request ["attachment"] as $attachment)
            {
                $note_attachment = NoteAttachment::create ([
                    "note_id" => $note->id,
                    "url" => $attachment ["url"]
                ]);
            }
        }
    }

    public static function create_from_request ($request, Activity $activity, Actor $actor)
    {
        $note = Note::create ([
            "note_id" => $request["id"]
        ]);
        TypeNote::update_from_request ($note, $request, $activity, $actor);

        $note->save ();
        return $note;
    }

    // some little functions
    public static function note_exists ($note_id)
    {
        return Note::where ("note_id", $note_id)->first ();
    }
}
