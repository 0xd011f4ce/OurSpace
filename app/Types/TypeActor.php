<?php

namespace App\Types;

use App\Models\User;
use App\Models\Actor;
use App\Models\Instance;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class TypeActor {
    public static function gen_keys ()
    {
        $config = [
            "private_key_bits" => 4096,
            "private_key_type" => OPENSSL_KEYTYPE_RSA
        ];

        $res = openssl_pkey_new ($config);
        openssl_pkey_export ($res, $private_key);

        $public_key = openssl_pkey_get_details ($res);

        return [
            "public_key" => $public_key,
            "private_key" => $private_key
        ];
    }

    public static function create_from_user (User $user)
    {
        $keys = TypeActor::gen_keys ();
        $app_url = env ("APP_URL");

        return [
            "user_id" => $user->id,

            "type" => "Person",
            "actor_id" => $app_url . "/ap/v1/user/" . $user->name,

            "following" => $app_url . "/ap/v1/user/" . $user->name . "/following",
            "followers" => $app_url . "/ap/v1/user/" . $user->name . "/followers",

            "liked" => $app_url . "/ap/v1/user/" . $user->name . "/liked",

            "inbox" => $app_url . "/ap/v1/user/" . $user->name . "/inbox",
            "outbox" => $app_url . "/ap/v1/user/" . $user->name . "/outbox",

            "sharedInbox" => $app_url . "/ap/v1/inbox",

            "preferredUsername" => $user->name,
            "name" => $user->name,
            "summary" => "",

            "public_key" => $keys["public_key"]["key"],
            "private_key" => $keys["private_key"]
        ];
    }

    public static function build_response (Actor $actor)
    {
        $response = [
            "@context" => [
                "https://www.w3.org/ns/activitystreams",
                "https://w3id.org/security/v1"
            ],
            "id" => $actor->actor_id,
            "type" => $actor->type,

            "following" => $actor->following,
            "followers" => $actor->followers,

            "liked" => $actor->liked,

            "inbox" => $actor->inbox,
            "outbox" => $actor->outbox,

            "endpoints" => [
                "sharedInbox" => $actor->sharedInbox,
            ],

            "preferredUsername" => $actor->preferredUsername,
            "name" => $actor->name,
            "summary" => $actor->summary,

            "icon" => [
                "type" => "Image",
                "mediaType" => "image/png",
                "url" => $actor->icon
            ],

            "image" => [
                "type" => "Image",
                "mediaType" => "image/png",
                "url" => $actor->image
            ],

            "publicKey" => [
                "id" => $actor->actor_id . "#main-key",
                "owner" => $actor->actor_id,
                "publicKeyPem" => $actor->public_key
            ]
        ];

        return $response;
    }

    public static function create_from_request ($request)
    {
        $actor = new Actor ();

        // Use null coalescing operator `??` for safety
        $actor->actor_id = $request['id'] ?? '';
        $actor->local_actor_id = TypeActor::actor_build_private_id ($actor->actor_id) ?? '';
        $actor->type = $request['type'] ?? '';

        $actor->following = $request['following'] ?? '';
        $actor->followers = $request['followers'] ?? '';

        $actor->liked = $request['liked'] ?? '';

        $actor->inbox = $request['inbox'] ?? '';
        $actor->outbox = $request['outbox'] ?? '';

        $actor->sharedInbox = $request['endpoints']['sharedInbox'] ?? '';

        $actor->preferredUsername = $request['preferredUsername'] ?? '';
        $actor->name = $request['name'] ?? '';
        $actor->summary = $request['summary'] ?? '';

        // Handle nested keys with checks
        $actor->icon = $request['icon']['url'] ?? '';
        $actor->image = $request['image']['url'] ?? '';

        // Handle nested keys in `publicKey`
        $actor->public_key = $request['publicKey']['publicKeyPem'] ?? '';

        $actor->save ();

        $instances = Instance::where ("inbox", $actor->sharedInbox);
        if (!$instances->first ())
        {
            $instance = new Instance ();
            $instance->inbox = $actor->sharedInbox;
            $instance->save ();
        }

        return $actor;
    }

    public static function obtain_actor_info ($actor_id)
    {
        $parsed_url = parse_url ($actor_id);
        $url_instance = $parsed_url["scheme"] . "://" . $parsed_url["host"];
        $url_path = explode ("/", $parsed_url["path"]);
        $actor_name = end ($url_path);

        $well_known = TypeActor::query_wellknown ($actor_name, $parsed_url ["host"]);

        foreach ($well_known->links as $link)
        {
        if ($link->rel == "self")
            {
                $client = new Client ();
                $res = $client->request ("GET", $link->href, [
                    "headers" => [
                        "Accept" => "application/json"
                    ]
                ]);
                $actor = json_decode ($res->getBody ()->getContents (), true);

                $result = TypeActor::create_from_request ($actor);
                return $result;
            }
        }

        return null;
    }

    public static function query_wellknown ($name, $domain)
    {
        $client = new Client ();

        $well_known_url = "https://" . $domain . "/.well-known/webfinger?resource=acct:" . $name . "@" . $domain;

        try {
            $res = $client->get ($well_known_url, [
                "headers" => [
                    "Accept" => "application/json"
                ]
            ]);
        } catch (\Exception $e) {
            return json_encode (["error" => "Actor not found"]);
        }

        return json_decode ($res->getBody ()->getContents ());
    }

    // some little functions
    public static function actor_exists ($actor_id)
    {
        $actor = Actor::where ("actor_id", $actor_id)->first ();
        return $actor;
    }

    public static function actor_exists_or_obtain ($actor_id)
    {
        $actor = TypeActor::actor_exists ($actor_id);
        if (!$actor)
        {
            $actor = TypeActor::obtain_actor_info ($actor_id);
        }

        return $actor;
    }

    public static function actor_exists_or_obtain_from_handle ($name, $domain)
    {
        $well_known = TypeActor::query_wellknown ($name, $domain);
        if (!$well_known)
            return null;

        foreach ($well_known->links as $link)
        {
            if ($link->rel == "self")
            {
                return TypeActor::actor_exists_or_obtain ($link->href);
            }
        }

        return null;
    }

    public static function actor_build_private_id ($actor_id)
    {
        $parsed_url = parse_url ($actor_id);
        $split_path = explode ("/", $parsed_url ["path"]);
        $username = end ($split_path);
        $domain = $parsed_url ["host"];

        return "@" . $username . "@" . $domain;
    }

    public static function actor_get_local ($actor_id)
    {
        $actor = Actor::where ("actor_id", $actor_id)->first ();
        if (!$actor->user)
            return null;
        return $actor;
    }
}
