<?php

namespace App\Types;

use App\Models\Note;
use App\Models\Hashtag;
use App\Models\Actor;
use App\Models\Activity;
use App\Models\NoteAttachment;
use App\Models\NoteMention;

use App\Events\NoteRepliedEvent;

use App\Notifications\UserNotification;

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
            "to" => $note->to,
            "cc" => $note->cc,
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

        $mentions = $note->get_mentions ()->get ();
        foreach ($mentions as $mention)
        {
            $response ["tag"] [] = [
                "type" => "Mention",
                "href" => $mention->actor->actor_id,
                "name" => $mention->actor->local_actor_id ?? "@" . $mention->actor->preferredUsername
            ];
        }

        return $response;
    }

    public static function craft_from_outbox (Actor $actor, $request)
    {
        // TODO: url should be route ('posts.show', $note->id)
        $private_id = uniqid ();

        $note = Note::create ([
            "actor_id" => $actor->id,
            "summary" => $request ["summary"],
            "note_id" => env ("APP_URL") . "/ap/v1/note/" . $private_id,
            "private_id" => $private_id,
            "in_reply_to" => $request ["inReplyTo"] ?? null,
            "type" => "Note",
            "summary" => $request ["summary"] ?? null,
            "attributedTo" => $actor->actor_id,
            "content" => $request ["content"] ?? null,
            "tag" => $request ["tag"] ?? null,

            // TODO: This should change when I implement visibilities and private notes
            "cc" => [
                $actor->followers
            ]
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

        if (isset ($request ["attachment"]) && $request ["attachment"])
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
                        "url" => $attachment ["url"],
                        "media_type" => $attachment ["mediaType"]
                    ]);
            }
        }

        if ($request ["inReplyTo"])
        {
            $parent_exists = Note::where ("note_id", $request ["inReplyTo"])->first ();
            if (!$parent_exists)
                $parent_exists = TypeNote::obtain_external ($request ["inReplyTo"]);

            $note->in_reply_to = $parent_exists ? $parent_exists->note_id : null;

            NoteRepliedEvent::dispatch ($activity, $actor, $parent_exists);
        }

        if (isset ($request ["tag"]) && $request ["tag"])
        {
            foreach ($request ["tag"] as $tag)
            {
                // TODO: refactor this, this code is shit but I want to get first working first
                switch ($tag ["type"])
                {
                    case "Hashtag":
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
                        break;

                    case "Mention":
                        $mention_name = $tag["name"];
                        $exploded_name = explode ("@", $mention_name);
                        $mention_actor = null;

                        $actor_exists = null;

                        $is_local = false;
                        if (count ($exploded_name) == 2)
                        {
                            // let's check if maybe it's local
                            $actor_exists = Actor::where ("preferredUsername", $exploded_name [1])->first ();
                            if (!$actor_exists)
                                continue;

                            $is_local = true;
                        }
                        else if (count ($exploded_name) == 3)
                        {
                            // maybe it's remote
                            $actor_exists = TypeActor::actor_exists_or_obtain_from_handle($exploded_name [1], $exploded_name [2]);
                            if ($actor_exists->user)
                                $is_local = true;
                        }
                        else
                            continue;

                        $mention = NoteMention::create ([
                            "note_id" => $note->id,
                            "actor_id" => $actor_exists->id
                        ]);

                        if ($is_local)
                        {
                            $actor_exists->user->notify (new UserNotification(
                                "Mention",
                                $actor->id,
                                $note->id
                            ));
                        }
                        break;
                }
            }
        }

        if (isset ($request ["replies"]))
        {
            // TODO: Handle replies
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
