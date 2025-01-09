<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfileAttachment extends Model
{
    protected $fillable = [
        "actor_id",
        "name",
        "content"
    ];

    public function actor ()
    {
        return $this->belongsTo (Actor::class);
    }
}
