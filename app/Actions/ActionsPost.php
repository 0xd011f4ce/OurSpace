<?php

namespace App\Actions;

use GuzzleHttp\Client;

use Illuminate\Support\Str;

use App\Models\Actor;
use App\Models\Activity;
use App\Models\Note;
use App\Models\NoteAttachment;

use Illuminate\Support\Facades\Storage;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ActionsPost
{
    public static function process_content_and_attachments ($request)
    {
        preg_match_all ("/#\w+/", $request->get ("content"), $tag_matches);
        $tags = $tag_matches [0];
        $processed_tags = [];

        foreach ($tags as $tag)
        {
            $processed_tags[] = [
                "type" => "Hashtag",
                "name" => $tag,
                "url" => route ("tags", ["tag" => $tag])
            ];
        }

        preg_match_all ("/@([a-zA-Z0-9_]+)(@[a-zA-Z0-9_.-]+)?/", $request->get ("content"), $mention_matches);
        $mentions = $mention_matches [0];
        $processed_mentions = [];

        foreach ($mentions as $mention)
        {
            $ats = explode ("@", $mention);
            $actor = null;

            if (count ($ats) == 2)
            {
                // it's a local user
                $actor = Actor::where ("preferredUsername", $ats [1])->first ();
                if (!$actor)
                    continue;
            }
            else
            {
                $actor = Actor::where ("local_actor_id", $mention)->first ();
                if (!$actor)
                    continue;
            }

            $processed_mentions[] = [
                "type" => "Mention",
                "href" => $actor->actor_id,
                "name" => $mention
            ];
        }

        $processed_content = Str::markdown ($request->get ("content"));
        $attachments = [];

        if ($request->hasFile ("files"))
        {
            $files = $request->file ("files");

            foreach ($files as $file)
            {
                $manager = new ImageManager (new Driver ());
                $image = $manager->read ($file);
                $image_data = $image->toJpeg ();

                $fname = $file->hashName () . uniqid () . ".jpg";
                Storage::disk ("public")->put ("images/" . $fname, $image_data);
                $attachments[] = env ("APP_URL") . "/storage/images/" . $fname;
            }
        }

        return [
            "summary" => $request->summary,
            "content" => $processed_content,
            "attachments" => $attachments,
            "inReplyTo" => $request->inReplyTo ?? null,
            "tags" => $processed_tags,
            "mentions" => $processed_mentions
        ];
    }

    public static function create_attachment (Note $note, $url)
    {
        $attachment = new NoteAttachment ();
        $attachment->note_id = $note->id;
        $attachment->url = $url;
        $attachment->save ();
    }

    public static function create_attachments (Note $note, $attachments)
    {
        foreach ($attachments as $attachment)
        {
            ActionsPost::create_attachment ($note, $attachment);
        }
    }

    public static function post_new ($request)
    {
        if (!auth ()->check ())
            return ["error" => "You must be logged in to post."];

        $processed = ActionsPost::process_content_and_attachments ($request);

        $actor = null;
        if ($request ["blog_id"])
            $actor = Actor::where ("blog_id", $request ["blog_id"])->first ();
        else
            $actor = auth ()->user ()->actor ()->first ();

        try {
            $client = new Client ();
            $response = $client->post ($actor->outbox, [
                "json" => [
                    "type" => "Post",
                    "summary" => $processed ["summary"],
                    "content" => $processed ["content"],
                    "attachments" => $processed ["attachments"],
                    "visibility" => $request ["visibility"],
                    "inReplyTo" => $processed ["inReplyTo"] ?? null,
                    "tags" => $processed ["tags"] ?? null,
                    "mentions" => $processed ["mentions"] ?? null
                ]
            ]);
        }
        catch (\Exception $e)
        {
            return ["error" => "Could not connect to server: " . $e->getMessage ()];
        }

        return ["success" => "Post created"];
    }

    public static function like_post (Actor $actor, Note $note)
    {
        $client = new Client ();

        try
        {
            $response = $client->post ($actor->outbox, [
                "json" => [
                    "type" => "Like",
                    "object" => $note->note_id,
                ]
            ]);
        }
        catch (\Exception $e)
        {
            return ["error" => "Could not connect to server: " . $e->getMessage ()];
        }

        return $response;
    }

    public static function boost_post (Actor $actor, Note $note)
    {
        $client = new Client ();

        try
        {
            $response = $client->post ($actor->outbox, [
                "json" => [
                    "type" => "Boost",
                    "object" => $note->note_id,
                ]
            ]);
        }
        catch (\Exception $e)
        {
            return ["error" => "Could not connect to server: " . $e->getMessage ()];
        }

        return $response;
    }

    public static function pin_post (Actor $actor, Note $note)
    {
        $client = new Client ();

        try
        {
            $response = $client->post ($actor->outbox, [
                "json" => [
                    "type" => "Pin",
                    "object" => $note->note_id,
                ]
            ]);
        }
        catch (\Exception $e)
        {
            return ["error" => "Could not connect to server: " . $e->getMessage ()];
        }
    }
}
