<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = [
        "activity_id",
        "actor_id",
        "note_id"
    ];

    public function activity ()
    {
        return $this->belongsTo(Activity::class);
    }

    public function actor ()
    {
        return $this->belongsTo(Actor::class);
    }

    public function note ()
    {
        return $this->belongsTo(Note::class);
    }
}
