<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $fillable = [
        "activity_id",
        "actor",
        "type",
        "object",
        "target",
        "summary"
    ];

    protected $casts = [
        "object" => "array",
        "target" => "array"
    ];

    public function actor ()
    {
        return $this->belongsTo (Actor::class);
    }
}
