<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NoteAttachment extends Model
{
    protected $fillable = [
        "note_id",
        "url",
        "media_type"
    ];
}
