<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    protected $fillable = [
        "activity_id",
        "actor_id",

        "note_id",
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

    public function attachments ()
    {
        return $this->hasMany (NoteAttachment::class);
    }
}
