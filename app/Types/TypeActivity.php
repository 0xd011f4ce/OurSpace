<?php

namespace App\Types;

use App\Models\Actor;
use App\Models\Activity;
use App\Models\Instance;

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
            "published" => $activity->created_at,
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

    public static function craft_delete (Actor $actor, $id)
    {
        $delete_activity = new Activity ();
        $delete_activity->activity_id = env ("APP_URL") . "/activity/" . uniqid ();
        $delete_activity->type = "Delete";
        $delete_activity->actor = $actor->actor_id;

        $delete_activity->object = [
            "id" => $id,
            "type" => "Tombstone"
        ];

        $delete_activity->save ();

        return $delete_activity;
    }

    public static function get_private_key (Actor $actor)
    {
        return openssl_get_privatekey ($actor->private_key);
    }

    public static function sign ($data, $key)
    {
        openssl_sign ($data, $signature, $key, OPENSSL_ALGO_SHA256);

        return $signature;
    }

    public static function craft_signed_headers ($activity, Actor $source, $target)
    {
        if (!$source->user)
        {
            Log::error ("Source not found");
            return null;
        }

        $key_id = $source->actor_id . "#main-key";

        $signer = TypeActivity::get_private_key ($source);

        $date = gmdate ("D, d M Y H:i:s \G\M\T");

        // we suppose that the activity is already json encoded
        $hash = hash ("sha256", $activity, true);
        $digest = base64_encode ($hash);

        $url = null;

        if ($target instanceof Actor)
            $url = parse_url ($target->inbox);
        else
            $url = parse_url ($target);

        if (!$url ["path"] || !$url ["host"])
        {
            Log::error ("Target not found");
            return null;
        }

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
            "B64" => $signature_b64
        ];
    }

    public static function post_activity (Activity $activity, Actor $source, $target, $should_sign = false)
    {
        $crafted_activity = TypeActivity::craft_response ($activity);

        if ($should_sign)
        {
            $crafted_activity["to"] = [
                "https://www.w3.org/ns/activitystreams#Public"
            ];

            $crafted_activity["cc"] = [
                $source->following
            ];

            $key = TypeActivity::get_private_key ($source);
            $activity_json = json_encode ($crafted_activity, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);
            $signature = TypeActivity::sign ($activity_json, $key);

            $crafted_activity ["signature"] = [
                "type" => "RsaSignature2017",
                "creator" => $source->actor_id . "#main-key",
                "created" => gmdate ("Y-m-d\TH:i:s\Z"),
                "signatureValue" => base64_encode ($signature)
            ];

            $activity_json = json_encode ($crafted_activity, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);
        }

        $activity_json = json_encode ($crafted_activity, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);
        $activity_json = mb_convert_encoding ($activity_json, "UTF-8");

        $headers = TypeActivity::craft_signed_headers ($activity_json, $source, $target);
        if (!$headers)
        {
            Log::error ("Failed to craft headers");
            return null;
        }

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
            $response = $e->getResponse ();
            if ($response)
            {
                Log::error ("Failed to post activity: " . $response->getBody ());
            }

            Log::error ("Failed to post activity: " . $e->getMessage ());
            return null;
        }

        return $response;
    }

    public static function post_to_instances (Activity $activity, Actor $source)
    {
        $instances = Instance::all ();
        foreach ($instances as $instance)
        {
            $response = TypeActivity::post_activity ($activity, $source, $instance->inbox, true);
            if ($response->getStatusCode () < 200 || $response->getStatusCode () >= 300)
            {
                Log::info ("failed to post activity to " . $instance->inbox);
            }
        }
    }

    // some little functions
    public static function activity_exists ($activity_id)
    {
        return Activity::where ("activity_id", $activity_id)->first ();
    }
}
