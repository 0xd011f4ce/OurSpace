<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

use App\Models\User;
use App\Models\Actor;
use App\Models\Note;
use App\Models\Blog;

use App\Actions\ActionsUser;
use App\Helpers\PaginationHelper;

class ProfileController extends Controller
{
    public function show ($user_name)
    {
        $actor = null;
        $user = null;

        if (str_starts_with ($user_name, "@")) {
            $actor = Actor::where ("local_actor_id", $user_name)->first ();
            if (!$actor)
                return redirect ()->route ("home");
        }
        else
        {
            $user = User::where ("name", $user_name)->first ();
            if (!$user)
                return redirect ()->route ("home");
            $actor = $user->actor;
        }

        return view ("users.profile", compact ("actor", "user"));
    }

    public function edit ()
    {
        if (!auth ()->check ()) {
            return redirect ()->route ("login");
        }

        $user = auth ()->user ();

        return view ("users.edit", compact ("user"));
    }

    public function update (Request $request)
    {
        if (!auth ()->check ()) {
            return redirect ()->route ("login");
        }

        $incoming_fields = $request->validate ([
            "avatar" => "image|max:4096",
            "song" => "file|mimes:audio/mpeg,mp3|max:4096",
            "notification_sound" => "file|mimes:audio/mpeg,mp3|max:1024",
            "bio" => "sometimes|nullable|string",
            "about_you" => "sometimes|nullable|string",
            "status" => "sometimes|nullable|string",
            "mood" => "sometimes|nullable|string",
            "general" => "sometimes|nullable|string",
            "music" => "sometimes|nullable|string",
            "movies" => "sometimes|nullable|string",
            "television" => "sometimes|nullable|string",
            "books" => "sometimes|nullable|string",
            "heroes" => "sometimes|nullable|string",
            "blurbs" => "sometimes|nullable|string"
        ]);

        $user = auth ()->user ();
        $fname = $user->id . "-" . uniqid ();

        $changing_avatar = false;
        $changing_song = false;
        $changing_notification_sound = false;

        $old_avatar = null;
        $old_song = null;
        $old_notification_sound = null;
        if (isset ($incoming_fields["avatar"]) && !empty ($incoming_fields["avatar"]))
        {
            $manager = new ImageManager (new Driver ());
            $image = $manager->read ($request->file ("avatar"));
            $image_data = $image->cover (256, 256)->toJpeg ();
            Storage::disk ("public")->put ("avatars/" . $fname . ".jpg", $image_data);

            $old_avatar = "avatars/" . $user->avatar;

            $user->avatar = $fname . ".jpg";
            $user->actor->icon = env ("APP_URL") . $user->avatar;

            $changing_avatar = true;
        }

        if (isset ($incoming_fields["song"]) && !empty ($incoming_fields["song"]))
        {
            $file = $request->file ("song");
            Storage::disk ("public")->put ("songs/" . $fname . ".mp3", file_get_contents ($file));

            $old_song = "songs/" . $user->profile_song;
            $user->profile_song = $fname . ".mp3";
            $changing_song = true;
        }

        if (isset ($incoming_fields ["notification_sound"]) && !empty ($incoming_fields["notification_sound"]))
        {
            $file = $request->file ("notification_sound");
            Storage::disk ("public")->put ("notification_sounds/" . $fname . ".mp3", file_get_contents ($file));

            $old_notification_sound = "notification_sounds/" . $user->notification_sound;
            $user->notification_sound = $fname . ".mp3";
            $changing_notification_sound = true;
        }

        $user->bio = $incoming_fields["bio"];
        $user->about_you = $incoming_fields["about_you"];
        $user->status = $incoming_fields["status"];
        $user->mood = $incoming_fields["mood"];

        $user->interests_general = $incoming_fields["general"];
        $user->interests_music = $incoming_fields["music"];
        $user->interests_movies = $incoming_fields["movies"];
        $user->interests_television = $incoming_fields["television"];
        $user->interests_books = $incoming_fields["books"];
        $user->interests_heroes = $incoming_fields["heroes"];

        $user->blurbs = $incoming_fields["blurbs"];
        $user->save ();

        $user->actor->summary = $user->bio;
        $user->actor->save ();

        if ($changing_avatar)
            Storage::disk ("public")->delete (str_replace ("/storage/", "", $old_avatar));

        if ($changing_song)
            Storage::disk ("public")->delete (str_replace ("/storage/", "", $old_song));

        if ($changing_notification_sound)
            Storage::disk ("public")->delete (str_replace ("/storage/", "", $old_notification_sound));

        $response = ActionsUser::update_profile ();
        if (isset ($response["error"]))
            return back ()->with ("error", "Error updating profile: " . $response["error"]);

        return back ()->with ("success", "Profile updated successfully!");
    }

    public function friends ($user_name)
    {
        // only local users
        if (str_starts_with ($user_name, "@"))
        {
            return redirect ()->route ("users.show", [ "user_name" => $user_name ]);
        }

        $user = User::where ("name", $user_name)->first ();
        if (!$user)
            return redirect ()->route ("home");

        $actor = $user->actor;

        $ids = $user->mutual_friends ();
        if (request ()->get ("query"))
        {
            $query = request ()->get ("query");
            $friends = Actor::whereIn ("actor_id", $ids)
                ->where ("preferredUsername", "like", "%" . $query . "%")
                ->get ();
        }
        else
        {
            $friends = Actor::whereIn ("actor_id", $ids)->get ();
        }

        return view ("users.friends", compact ("actor", "user", "friends"));
    }

    public function notifications ()
    {
        if (!auth ()->check ())
            return redirect ()->route ("login");

        $user = auth ()->user ();
        $user_notifications = $user->notifications;
        $notifications = PaginationHelper::paginate (collect ($user_notifications), 20);
        $unread_notifications = $user->unreadNotifications->count ();
        $processed_notifications = [];

        foreach ($notifications as $notification)
        {
            $data = $notification->data;
            $type = $data ['type'];
            $actor = Actor::find ($data["actor"]);
            $object = null;

            switch ($type)
            {
                case "Follow":
                case "Unfollow":
                    $object = Actor::find ($data["object"]);
                    break;

                case "Signup":
                    $object = User::find ($data ["object"]);
                    break;

                case "Like":
                case "Reply":
                case "Boost":
                case "Mention":
                    $object = Note::find ($data["object"]);
                    break;
            }

            $processed_notifications[] = [
                "id"=> $notification->id,
                "type" => $type,
                "actor" => $actor,
                "object" => $object,
                "created_at" => $notification->created_at,
                "read_at" => $notification->read_at
            ];

            $notification->markAsRead ();
        }

        return view ("users.notifications", compact ("user", "notifications", "processed_notifications", "unread_notifications"));
    }

    public function blogs ($user_name)
    {
        if (str_starts_with ($user_name, "@"))
        {
            return redirect ()->route ("users.show", [ "user_name" => $user_name ]);
        }

        $user = User::where ("name", $user_name)->first ();
        if (!$user)
            return redirect ()->route ("home");

        $blogs = Blog::where ("user_id", $user->id)->orderBy ("created_at", "desc")->get ();

        return view ("users.blogs", compact ("user", "blogs"));
    }
}
