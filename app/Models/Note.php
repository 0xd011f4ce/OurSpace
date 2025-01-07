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
    ];

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

    public function attachments ()
    {
        return $this->hasMany (NoteAttachment::class);
    }
}
