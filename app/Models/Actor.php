<?php

namespace App\Models;

use App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class Actor extends Model
{
    protected $fillable = [
        "user_id",

        "type",
        "actor_id",

        "following",
        "followers",

        "liked",

        "inbox",
        "outbox",

        "sharedInbox",

        "preferredUsername",
        "name",
        "summary",

        "public_key",
        "private_key"
    ];

    public function user ()
    {
        return $this->belongsTo (User::class);
    }

    public function create_from_user (User $user)
    {
        $app_url = Config::get ("app.url");

        $config = [
            "private_key_bits" => 4096,
            "private_key_type" => OPENSSL_KEYTYPE_RSA
        ];

        $res = openssl_pkey_new ($config);
        openssl_pkey_export ($res, $private_key);

        $public_key = openssl_pkey_get_details ($res);

        return $this->create ([
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

            "public_key" => $public_key["key"],
            "private_key" => $private_key
        ]);
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

            "sharedInbox" => $actor->sharedInbox,

            "preferredUsername" => $actor->preferredUsername,
            "name" => $actor->name,
            "summary" => $actor->summary,

            "icon" => [
                "type" => "Image",
                "mediaType" => "image/jpeg",
                "url" => $actor->icon
            ],

            "image" => [
                "type" => "Image",
                "mediaType" => "image/jpeg",
                "url" => $actor->icon
            ],

            "publicKey" => [
                "id" => $actor->actor_id . "#main-key",
                "owner" => $actor->actor_id,
                "publicKeyPem" => $actor->public_key
            ]
        ];

        return $response;
    }
}
