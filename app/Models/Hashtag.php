<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hashtag extends Model
{
    protected $fillable = [
        "name"
    ];

    public function get_notes () {
        return $this->belongsToMany(Note::class, 'note_hashtag')->orderBy('created_at', 'desc');
    }
}
