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
    public static function post_new ($request)
    {
        if (!auth ()->check ())
            return ["error" => "You must be logged in to post."];

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

        $actor = auth ()->user ()->actor ()->first ();

        try {
            $client = new Client ();
            $response = $client->post ($actor->outbox, [
                "json" => [
                    "type" => "Post",
                    "content" => $processed_content,
                    "attachments" => $attachments,
                ]
            ]);
        }
        catch (\Exception $e)
        {
            return ["error" => "Could not connect to server."];
        }

        return ["success" => "Post created"];
    }
}
