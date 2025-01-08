<?php

namespace App\Models;

use App\Models\User;
use App\Models\Announcement;
use App\Models\Note;

use App\Helpers\PaginationHelper;

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

    public function get_posts ()
    {
        $posts = $this->hasMany (Note::class, "actor_id")->orderBy ("created_at", "desc")->get ();
        $announcements = $this->hasMany (Announcement::class, "actor_id")->orderBy ("created_at", "desc")->get ();

        $all = $posts->merge ($announcements)->sortByDesc ("created_at");

        return PaginationHelper::paginate ($all);
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
        $following = Follow::where ("actor", $this->id)->where ("object", $actor->id)->first ();
        $followers = Follow::where ("actor", $actor->id)->where ("object", $this->id)->first ();

        return $following && $followers;
    }

    public function liked_note (Note $note)
    {
        return Like::where ("actor_id", $this->id)->where ("note_id", $note->id)->first ();
    }

    public function boosted_note (Note $note)
    {
        return Announcement::where ("actor_id", $this->id)->where ("note_id", $note->id)->first ();
    }
}
