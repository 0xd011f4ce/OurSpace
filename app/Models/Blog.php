<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    protected $fillable = [
        "name",
        "slug",
        "description",
        "icon",
        "user_id",
        "actor_id",
        "blog_category_id"
    ];

    public function user ()
    {
        return $this->belongsTo (User::class);
    }

    public function actor ()
    {
        return $this->belongsTo (Actor::class);
    }

    public function notes ()
    {
        return $this->hasMany (Note::class, "actor_id", "actor_id");
    }

    public function pinned_notes ()
    {
        return $this->hasMany (ProfilePin::class, "actor_id", "actor_id");
    }
}
