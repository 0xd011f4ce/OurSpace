<?php

namespace App\Models;

use App\Models\User;
use App\Types\TypeActor;

use Illuminate\Database\Eloquent\Model;

class Actor extends Model
{
    protected $fillable = [
        "user_id",

        "type",
        "actor_id",
        "local_actor_id",

        "following",
        "followers",

        "liked",

        "inbox",
        "outbox",

        "sharedInbox",

        "preferredUsername",
        "name",
        "summary",

        "icon",
        "image",

        "public_key",
        "private_key"
    ];

    public function user ()
    {
        return $this->belongsTo (User::class);
    }

    public function create_from_user (User $user)
    {
        $data = TypeActor::create_from_user ($user);
        return $this->create ($data);
    }

    public static function build_response (Actor $actor)
    {
        return TypeActor::build_response ($actor);
    }
}
