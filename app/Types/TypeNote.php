<?php

namespace App\Types;

use App\Models\Note;
use App\Models\Hashtag;
use App\Models\Actor;
use App\Models\Activity;
use App\Models\NoteAttachment;

use GuzzleHttp\Client;

use Illuminate\Support\Facades\Log;

class TypeNote
{
    public static function build_response (Note $note)
    {
        $author = $note->get_actor ()->first ();

        $response = [
            "id" => $note->note_id,
            "type" => "Note",
            "summary" => $note->summary,
            "inReplyTo" => $note->in_reply_to,
            "published" => $note->created_at,
            "updated" => $note->updated_at,
            "url" => $note->url,
            "attributedTo" => $note->attributedTo,
            "to" => [
                "https://www.w3.org/ns/activitystreams#Public"
            ],
            "cc" => [
                $author->following
            ],
            "content" => $note->content
        ];

        $attachments = $note->attachments ()->get ();
        foreach ($attachments as $attachment)
        {
            $response ["attachment"] [] = [
                "type" => "Document",
                "mediaType" => "image/jpeg",
                "url" => $attachment->url
            ];
        }

        $tags = $note->get_hashtags ()->get ();
        foreach ($tags as $tag)
        {
            $response ["tag"] [] = [
                "type" => "Hashtag",
                "name" => $tag->name,
                "url" => route ("tags", ["tag" => $tag->name])
            ];
        }

        return $response;
    }

    public static function craft_from_outbox (Actor $actor, $request)
    {
        // TODO: url should be route ('posts.show', $note->id)
        $note = Note::create ([
            "actor_id" => $actor->id,
            "summary" => $request ["summary"],
            "note_id" => env ("APP_URL") . "/ap/v1/note/" . uniqid (),
            "in_reply_to" => $request ["inReplyTo"] ?? null,
            "type" => "Note",
            "summary" => $request ["summary"] ?? null,
            "attributedTo" => $actor->actor_id,
            "content" => $request ["content"] ?? null,
            "tag" => $request ["tag"] ?? null
        ]);

        $note->url = route ('posts.show', $note->id);
        $note->save ();

        return $note;
    }

    public static function update_from_request (Note $note, $request, Activity $activity = null, Actor $actor = null)
    {
        if ($activity)
            $note->activity_id = $activity->id;

        if ($actor)
            $note->actor_id = $actor->id;
        else
        {
            $actor = TypeActor::actor_exists ($request ["attributedTo"]);
            if (!$actor)
            {
                $actor = TypeActor::obtain_actor_info($request ["attributedTo"]);
                if (!$actor)
                {
                    Log::error ("TypeNote::update_from_request: Could not obtain actor info.");
                }
            }

            $note->actor_id = $actor->id;
        }

        $note->note_id = $request["id"] ?? null;
        // $note->in_reply_to = $request["inReplyTo"] ?? null;
        $note->summary = $request["summary"] ?? null;
        $note->url = $request["url"] ?? null;
        $note->attributedTo = $request["attributedTo"] ?? null;
        $note->content = $request["content"] ?? null;
        $note->tag = $request["tag"] ?? null;
        $note->created_at = $request["published"] ?? null;

        $attachments = $note->attachments ()->get ();
        foreach ($attachments as $attachment)
            $attachment->delete ();

        if ($request ["attachment"])
        {

            foreach ($request ["attachment"] as $attachment)
            {
                // TODO: Check it's type and proceed based on that
                // TODO: Store its type in the database
                $attachment_url = $attachment ["url"];
                $exists = NoteAttachment::where ("url", $attachment_url)->first ();
                if (!$exists)
                    $note_attachment = NoteAttachment::create ([
                        "note_id" => $note->id,
                        "url" => $attachment ["url"]
                    ]);
            }
        }

        if ($request ["tag"])
        {
            foreach ($request ["tag"] as $tag)
            {
                if ($tag ["type"] != "Hashtag")
                    continue;

                $tag_name = $tag ["name"];

                $hashtag_exists = Hashtag::where ("name", $tag_name)->first ();
                if ($hashtag_exists)
                {
                    $note->get_hashtags ()->attach ($hashtag_exists->id);
                    continue;
                }

                $hashtag = Hashtag::create ([
                    "name" => $tag_name
                ]);
                $note->get_hashtags ()->attach ($hashtag->id);
            }
        }

        if ($request ["inReplyTo"])
        {
            $parent_exists = Note::where ("note_id", $request ["inReplyTo"])->first ();
            if (!$parent_exists)
            {
                $parent = TypeNote::obtain_external ($request ["inReplyTo"]);
                if ($parent)
                    $note->in_reply_to = $parent->note_id;
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

    public static function obtain_external ($note_id)
    {
        $note = Note::where ("note_id", $note_id)->first ();
        if ($note)
            return $note;

        try {
            $client = new Client ();
            $res = $client->request ("GET", $note_id, [
                "headers" => [
                    "Accept" => "application/activity+json"
                ]
            ]);
            $body = $res->getBody ()->getContents ();

            $note = Note::create ([
                "note_id" => $note_id
            ]);
            TypeNote::update_from_request ($note, json_decode ($body, true));
            $note->save ();
        }
        catch (\Exception $e)
        {
            Log::error ("TypeNote::obtain_external: " . $e->getMessage ());
            return null;
        }

        return $note;
    }

    // some little functions
    public static function note_exists ($note_id)
    {
        return Note::where ("note_id", $note_id)->first ();
    }
}
