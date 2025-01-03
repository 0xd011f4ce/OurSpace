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
        $followers = Activity::where ("type", "Follow")->where ("object", '"' . $this->actor->actor_id . '"')->pluck ("actor")->toArray ();
        $following = Activity::where ("type", "Follow")->where ("actor", $this->actor->actor_id)->pluck ("object")->toArray ();

        return array_intersect ($followers, $following);
    }

    public function received_requests ()
    {
        // users following me, where I am the object and I retrieve the actors
        $following = Activity::where ("type", "Follow")
            ->where ("object", '"' . $this->actor->actor_id . '"') // i am the object being followed
            ->pluck ("actor")
            ->map (fn ($actor) => json_encode ($actor, JSON_UNESCAPED_SLASHES))
            ->toArray ();

        // users i am following, where I am the actor and I retrieve the objects
        $followers = Activity::where ("type", "Follow")
            ->whereIn ("object", $following) // actors
            ->where ("actor", $this->actor->actor_id) // following me
            ->pluck ("actor")->toArray ();

        return array_diff ($following, $followers);
    }

    public function sent_requests ()
    {
        // users i am following, where I am the actor and I retrieve the objects
        $followers = Activity::where ("type", "Follow")
            ->where ("actor", $this->actor->actor_id) // actors I follow
            ->pluck ("object")->toArray ();

        // users following me, where I am the object and I retrieve the actors
        $following = Activity::where ("type", "Follow")
            ->whereIn ("actor", $followers) // actors
            ->where ("object", '"' . $this->actor->actor_id . '"') // that following me
            ->pluck ("actor")->toArray ();

        return array_diff ($followers, $following);
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
