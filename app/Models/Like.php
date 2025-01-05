<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    protected $fillable = [
        "activity_id",
        "note_id",
        "actor_id"
    ];

    public function get_activity ()
    {
        return $this->belongsTo (Activity::class, "activity_id");
    }

    public function get_note ()
    {
        return $this->belongsTo (Note::class, "note_id");
    }

    public function get_actor ()
    {
        return $this->belongsTo (Actor::class, "actor_id");
    }
}
