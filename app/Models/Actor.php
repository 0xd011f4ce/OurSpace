<?php

namespace App\Models;

use App\Models\User;
use App\Models\Activity;
use App\Models\Note;

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

    protected $hidden = [
        "id",
        "user_id",
        "created_at",
        "updated_at",
        "private_key"
    ];

    public function user ()
    {
        return $this->belongsTo (User::class);
    }

    public function posts ()
    {
        return $this->hasMany (Note::class, "actor_id")->orderBy ("created_at", "desc");
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

    public function friends_with (Actor $actor)
    {
        $self_id = '"' . str_replace ("/", "\/", $this->actor_id) . '"';
        $other_id = '"' . str_replace ("/", "\/", $actor->actor_id) . '"';

        $following = Activity::where ("actor", $this->actor_id)->where ("type", "Follow")->where ("object", $other_id)->first ();
        $followers = Activity::where ("actor", $actor->actor_id)->where ("type", "Follow")->where ("object", $self_id)->first ();

        return $following && $followers;
    }
}
