<?php

namespace App\Types;

use App\Models\Actor;
use App\Models\Activity;

use GuzzleHttp\Client;

use Illuminate\Support\Facades\Log;

class TypeActivity {
    public static function craft_response (Activity $activity)
    {
        $crafted_activity = [
            "@context" => "https://www.w3.org/ns/activitystreams",
            "id" => $activity->activity_id,
            "type" => $activity->type,
            "actor" => $activity->actor,
            "object" => $activity->object,
        ];

        if ($activity->target)
        {
            $crafted_activity["target"] = $activity->target;
        }

        if ($activity->summary)
        {
            $crafted_activity["summary"] = $activity->summary;
        }

        return $crafted_activity;
    }

    public static function craft_accept (Activity $activity)
    {
        $accept_activity = new Activity ();
        $accept_activity->activity_id = env ("APP_URL") . "/activity/" . uniqid ();
        $accept_activity->type = "Accept";
        $accept_activity->actor = $activity->object;
        $accept_activity->object = TypeActivity::craft_response ($activity);
        $accept_activity->save ();

        return $accept_activity;
    }

    public static function craft_undo (Activity $activity, Actor $self)
    {
        $undo_activity = new Activity ();
        $undo_activity->activity_id = env ("APP_URL") . "/activity/" . uniqid ();
        $undo_activity->type = "Undo";
        $undo_activity->actor = $self->actor_id;
        $undo_activity->object = $activity;
        $undo_activity->save ();

        return $undo_activity;
    }

    public static function craft_follow (Actor $actor, Actor $object)
    {
        $follow_activity = new Activity ();
        $follow_activity->activity_id = env ("APP_URL") . "/activity/" . uniqid ();
        $follow_activity->type = "Follow";
        $follow_activity->actor = $actor->actor_id;
        $follow_activity->object = $object->actor_id;
        $follow_activity->save ();

        return $follow_activity;
    }

    public static function craft_update (Actor $actor, $fields)
    {
        $update_activity = new Activity ();
        $update_activity->activity_id = env ("APP_URL") . "/activity/" . uniqid ();
        $update_activity->type = "Update";
        $update_activity->actor = $actor->actor_id;
        $update_activity->object = $fields;
        $update_activity->save ();

        return $update_activity;
    }

    public static function craft_create (Actor $actor, $fields)
    {
        $create_activity = new Activity ();
        $create_activity->activity_id = env ("APP_URL") . "/activity/" . uniqid ();
        $create_activity->type = "Create";
        $create_activity->actor = $actor->actor_id;

        switch ($fields ["type"])
        {
            case "Note":
                $create_activity->object = TypeNote::build_response ($fields);
                break;
        }

        $create_activity->save ();

        return $create_activity;
    }

    public static function craft_signed_headers ($activity, Actor $source, $target)
    {
        if (!$source->user)
        {
            Log::error ("Source not found");
            return null;
        }

        $key_id = $source->actor_id . "#main-key";

        $signer = openssl_get_privatekey ($source->private_key);

        $date = gmdate ("D, d M Y H:i:s \G\M\T");

        // we suppose that the activity is already json encoded
        $hash = hash ("sha256", $activity, true);
        $digest = base64_encode ($hash);

        $url = null;

        if ($target instanceof Actor)
            $url = parse_url ($target->inbox);
        else
            $url = parse_url ($target);

        $string_to_sign = "(request-target): post ". $url["path"] . "\nhost: " . $url["host"] . "\ndate: " . $date . "\ndigest: SHA-256=" . $digest;

        openssl_sign ($string_to_sign, $signature, $signer, OPENSSL_ALGO_SHA256);
        $signature_b64 = base64_encode ($signature);

        $signature_header = 'keyId="' . $key_id . '",algorithm="rsa-sha256",headers="(request-target) host date digest",signature="' . $signature_b64 . '"';

        return [
            "Host" => $url["host"],
            "Date" => $date,
            "Digest" => "SHA-256=" . $digest,
            "Signature" => $signature_header,
            "Content-Type" => "application/activity+json",
            "Accept" => "application/activity+json",
        ];
    }

    public static function post_activity (Activity $activity, Actor $source, $target)
    {
        $crafted_activity = TypeActivity::craft_response ($activity);
        $activity_json = json_encode ($crafted_activity, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);
        $activity_json = mb_convert_encoding ($activity_json, "UTF-8");

        $headers = TypeActivity::craft_signed_headers ($activity_json, $source, $target);

        try {
            $target_inbox = null;

            if ($target instanceof Actor)
            {
                $target_inbox = $target->inbox;
            }
            else
            {
                $target_inbox = $target;
            }

            $client = new Client ();
            $response = $client->post ($target_inbox, [
                "headers" => $headers,
                "body" => $activity_json,
                "debug" => true
            ]);
        }
        catch (RequestException $e)
        {
            Log::error ($e->getMessage ());
            return null;
        }

        return $response;
    }

    // some little functions
    public static function activity_exists ($activity_id)
    {
        return Activity::where ("activity_id", $activity_id)->first ();
    }
}
