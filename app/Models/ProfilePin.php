<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfilePin extends Model
{
    protected $fillable = [
        "activity_id",
        "note_id",
        "actor_id"
    ];

    public function activity ()
    {
        return $this->belongsTo (Activity::class);
    }

    public function note ()
    {
        return $this->belongsTo (Note::class);
    }

    public function actor ()
    {
        return $this->belongsTo (Actor::class);
    }
}
