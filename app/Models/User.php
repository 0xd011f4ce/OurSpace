<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',

        "bio",
        "avatar",
        "status",
        "mood",
        "about_you",

        // interests
        "interests_general",
        "interests_music",
        "interests_movies",
        "interests_television",
        "interests_books",
        "interests_heroes"
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected function avatar () : Attribute
    {
        return Attribute::make (get: function ($value) {
            return $value ? "/storage/avatars/" . $value : "/resources/img/default.jpg";
        });
    }

    public function actor ()
    {
        return $this->hasOne (Actor::class);
    }

    public function mutual_friends ()
    {
        $actor_id = '"' . str_replace ("/", "\/", $this->actor->actor_id) . '"';

        $followers = Activity::where ("type", "Follow")->where ("object", $actor_id)->pluck ("actor")->toArray ();
        $following = Activity::where ("type", "Follow")->where ("actor", $this->actor->actor_id)->pluck ("object")->toArray ();

        return array_intersect ($followers, $following);
    }

    public function friend_requests ()
    {
        $actor_id = '"' . str_replace ("/", "\/", $this->actor->actor_id) . '"';

        $followers = Activity::where ("type", "Follow")->where ("object", $actor_id)->pluck ("actor")->toArray ();
        $following = Activity::where ("type", "Follow")->where ("actor", $this->actor->actor_id)->pluck ("object")->toArray ();

        return array_diff ($followers, $following);
    }
}
