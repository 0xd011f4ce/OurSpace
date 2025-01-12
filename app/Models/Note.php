<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    protected $fillable = [
        "activity_id",
        "actor_id",

        "note_id",
        "private_id",
        "in_reply_to",
        "type",
        "summary",
        "url",
        "attributedTo",
        "content",
        "tag",
        "to",
        "cc",
        "visibility"
    ];

    protected $casts = [
        "to" => "array",
        "cc" => "array"
    ];

    public function setToAttribute ($value)
    {
        $this->attributes["to"] = json_encode ($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);
    }

    public function setCcAttribute ($value)
    {
        $this->attributes["cc"] = json_encode ($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);
    }

    public function get_activity ()
    {
        return $this->hasOne (Activity::class, "id", "activity_id");
    }

    public function get_actor ()
    {
        return $this->hasOne (Actor::class, "id", "actor_id");
    }

    public function get_likes ()
    {
        return $this->hasMany (Like::class);
    }

    public function get_boosts ()
    {
        return $this->hasMany (Announcement::class);
    }

    public function get_replies ()
    {
        return $this->hasMany (Note::class, "in_reply_to", "note_id");
    }

    public function get_parent ()
    {
        if ($this->in_reply_to)
            return $this->hasOne (Note::class, "note_id", "in_reply_to");
    }

    public function get_hashtags ()
    {
        return $this->belongsToMany (Hashtag::class, "note_hashtag");
    }

    public function get_mentions ()
    {
        return $this->hasMany (NoteMention::class);
    }

    public function attachments ()
    {
        return $this->hasMany (NoteAttachment::class);
    }

    public function is_pinned (Actor $actor)
    {
        return ProfilePin::where ("actor_id", $actor->id)->where ("note_id", $this->id)->first ();
    }

    public function can_view (Actor $actor = null)
    {
        $final_actor = $actor;
        $note_actor = $this->get_actor ()->first ();
        if (!$final_actor && auth ()->check ())
        {
            $final_actor = auth ()->user ()->actor;
        }

        if ($this->visibility == "public")
        {
            return true;
        }
        else if ($this->visibility == "followers" && $final_actor)
        {
            return $final_actor->friends_with ($note_actor);
        }
        else if ($this->visibility == "private" && $final_actor)
        {
            if ($final_actor == $note_actor)
                return true;

            $mention_exists = NoteMention::where ("note_id", $this->id)->where ("actor_id", $final_actor->id)->first ();
            if ($mention_exists)
                return true;
        }

        return false;
    }
}
