<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NoteMention extends Model
{
    protected $fillable = [
        "note_id",
        "actor_id"
    ];

    public function note ()
    {
        return $this->belongsTo (Note::class);
    }

    public function actor ()
    {
        return $this->belongsTo (Actor::class);
    }
}
