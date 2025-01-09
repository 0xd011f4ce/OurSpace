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
        "summary",
        "to",
        "cc"
    ];

    protected $casts = [
        "object" => "array",
        "target" => "array",
        "to" => "array",
        "cc" => "array"
    ];

    public function setObjectAttribute ($value)
    {
        $this->attributes["object"] = json_encode ($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);
    }

    public function setToAttribute ($value)
    {
        $this->attributes["to"] = json_encode ($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);
    }

    public function setCcAttribute ($value)
    {
        $this->attributes["cc"] = json_encode ($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);
    }

    public function actor ()
    {
        return $this->belongsTo (Actor::class);
    }
}
