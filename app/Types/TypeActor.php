<?php

namespace App\Types;

use App\Models\User;
use App\Models\Actor;
use App\Models\Instance;
use App\Models\ProfilePin;

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
            "featured" => $app_url . "/ap/v1/user/" . $user->name . "/collections/featured",
            "featured_tags" => $app_url . "/ap/v1/user/" . $user->name . "/collections/featured/tags",

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
                "https://w3id.org/security/v1",
                [
                    "featured" => [
                        "@id" => "http://joinmastodon.org/ns#featured",
                        "@type" => "@id"
                    ],
                    "featuredTags" => [
                        "@id" => "http://joinmastodon.org/ns#featuredTags",
                        "@type" => "@id"
                    ]
                ]
            ],
            "id" => $actor->actor_id,
            "type" => $actor->type,

            "following" => $actor->following,
            "followers" => $actor->followers,

            "liked" => $actor->liked,
            "featured" => $actor->featured,
            "featuredTags" => $actor->featured_tags,

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

            "published" => $actor->created_at,
            "updated" => $actor->updated_at,

            "publicKey" => [
                "id" => $actor->actor_id . "#main-key",
                "owner" => $actor->actor_id,
                "publicKeyPem" => $actor->public_key
            ]
        ];

        if ($actor->user)
        {
            // appent to @context
            $response ["@context"][] = [
                "schema" => "http://schema.org#",
                "PropertyValue" => "schema:PropertyValue",
                "value" => "schema:value"
            ];

            $response ["attachment"] = [
                [
                    "type" => "PropertyValue",
                    "name" => "Interests General",
                    "value" => $actor->user->interests_general
                ],
                [
                    "type" => "PropertyValue",
                    "name" => "Interests Music",
                    "value" => $actor->user->interests_music
                ],
                [
                    "type" => "PropertyValue",
                    "name" => "Interests Movies",
                    "value" => $actor->user->interests_movies
                ],
                [
                    "type" => "PropertyValue",
                    "name" => "Interests Television",
                    "value" => $actor->user->interests_television
                ],
                [
                    "type" => "PropertyValue",
                    "name" => "Interests Books",
                    "value" => $actor->user->interests_books
                ],
                [
                    "type" => "PropertyValue",
                    "name" => "Interests Heroes",
                    "value" => $actor->user->interests_heroes
                ]
            ];
        }

        return $response;
    }

    public static function update_from_request (Actor $actor, $request)
    {
        // Use null coalescing operator `??` for safety
        $actor->actor_id = $request['id'] ?? '';
        $actor->local_actor_id = TypeActor::actor_build_private_id ($actor->actor_id) ?? '';
        $actor->type = $request['type'] ?? '';

        $actor->following = $request['following'] ?? '';
        $actor->followers = $request['followers'] ?? '';

        $actor->liked = $request['liked'] ?? '';
        $actor->featured = $request['featured'] ?? '';
        $actor->featured_tags = $request['featuredTags'] ?? '';

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

        $instances = Instance::where ("inbox", $actor->sharedInbox);
        if (!$instances->first () && $actor->sharedInbox)
        {
            $instance = new Instance ();
            $instance->inbox = $actor->sharedInbox;
            $instance->save ();
        }

        $featured_items = TypeActor::actor_process_featured ($actor);
        ProfilePin::where ("actor_id", $actor->id)->delete ();

        foreach ($featured_items as $item)
        {
            if ($item ["type"] == "Note")
            {
                $note = TypeNote::note_exists ($item ["id"]);
                if (!$note)
                    $note = TypeNote::obtain_external ($item ["id"]);

                if (!$note)
                    continue;

                ProfilePin::create ([
                    "actor_id" => $actor->id,
                    "note_id" => $note->id
                ]);
            }
        }

        return $actor;
    }

    public static function create_from_request ($request)
    {
        $actor = new Actor ();
        TypeActor::update_from_request ($actor, $request);

        $actor->save ();
        return $actor;
    }

    public static function obtain_actor_info ($actor_id)
    {
        $parsed_url = parse_url ($actor_id);
        $url_instance = $parsed_url["scheme"] . "://" . $parsed_url["host"];
        $url_path = explode ("/", $parsed_url["path"]);
        $actor_name = end ($url_path);

        $well_known = TypeActor::query_wellknown ($actor_name, $parsed_url ["host"]);

        if (isset ($well_known->links))
        {
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
        }
        else
        {
            $client = new Client ();
            $res = $client->request ("GET", $actor_id, [
                "headers" => [
                    "Accept" => "application/activity+json"
                ]
            ]);
            $actor = json_decode ($res->getBody ()->getContents (), true);

            $result = TypeActor::create_from_request ($actor);
            return $result;
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
            // TODO: check if we got a 404
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

    public static function actor_process_featured (Actor $actor)
    {
        $pinned = [];

        if (!$actor->featured)
            return $pinned;

        return TypeActor::actor_process_ordered_collection ($actor->featured);
    }

    public static function actor_process_ordered_collection ($collection_link)
    {
        $items = [];

        try
        {
            $client = new Client ();
            $response = $client->get ($collection_link, [
                "headers" => [
                    "Accept" => "application/json"
                ]
            ]);

            $collection = json_decode ($response->getBody ()->getContents (), true);

            if (isset ($collection ["first"]) && isset ($collection ["last"]))
            {
                $first = $collection["first"];
                $last = $collection["last"];

                $current_url = $first;
                $current_page = 1;
                do {
                    $items = array_merge ($items, TypeActor::actor_processed_order_collection_page ($current_url));

                    $current_page++;
                    $current_url = $collection_link . "?page=" . $current_page;
                } while ($current_url != $last);
            }
            else
            {
                return $collection["orderedItems"];
            }
        }
        catch (\Exception $e)
        {
            Log::error ("TypeActor::actor_process_ordered_collection: " . $e->getMessage ());
        }

        return $items;
    }

    public static function actor_processed_order_collection_page ($page_link)
    {
        $items = [];

        try
        {
            $client = new Client ();
            $response = $client->get ($page_link, [
                "headers" => [
                    "Accept" => "application/json"
                ]
            ]);

            $collection = json_decode ($response->getBody ()->getContents (), true);
            foreach ($collection["orderedItems"] as $item)
            {
                $items[] = $item;
            }
        }
        catch (\Exception $e)
        {
            Log::error ("TypeActor::actor_processed_order_collection_page: " . $e->getMessage ());
        }

        return $items;
    }
}
