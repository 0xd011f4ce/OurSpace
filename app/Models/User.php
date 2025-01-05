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
        $followers = Follow::where ("actor", $this->actor->id)->pluck ("object")->toArray ();
        $following = Follow::where ("object", $this->actor->id)->pluck ("actor")->toArray ();

        $mutuals = array_intersect ($followers, $following);
        $actors = [];

        foreach ($mutuals as $id)
        {
            $actors[] = Actor::where ("id", $id)->first ()->actor_id;
        }

        return $actors;
    }

    public function received_requests ()
    {
        // users following me, that I am not following them
        $followers = Follow::where ("object", $this->actor->id)->pluck ("actor")->toArray ();
        $following = Follow::where ("actor", $this->actor->id)->pluck ("object")->toArray ();

        $diff = array_diff ($followers, $following);

        $actors = [];
        foreach ($diff as $id)
        {
            $actors[] = Actor::where ("id", $id)->first ()->actor_id;
        }
        return $actors;
    }

    public function sent_requests ()
    {
        // users i am following
        $followers = Follow::where ("actor", $this->actor->id)->pluck ("object")->toArray ();
        $following = Follow::where ("object", $this->actor->id)->pluck ("actor")->toArray ();

        $diff = array_diff ($followers, $following);

        $actors = [];
        foreach ($diff as $id)
        {
            $actors[] = Actor::where ("id", $id)->first ()->actor_id;
        }

        return $actors;
    }

    public function feed ()
    {
        $mutual_friends = $this->mutual_friends ();
        $friends_id = [
            $this->actor ()->first ()->id
        ];

        foreach ($mutual_friends as $friend)
        {
            $friends_id[] = Actor::where ("actor_id", $friend)->first ()->id;
        }

        $notes = Note::whereIn ("actor_id", $friends_id)->orderBy ("created_at", "desc")->get ();

        return $notes;
    }
}
