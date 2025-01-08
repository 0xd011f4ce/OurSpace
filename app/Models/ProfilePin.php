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
}
