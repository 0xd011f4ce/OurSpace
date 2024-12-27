<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

use App\Models\User;

class ProfileController extends Controller
{
    public function show (User $user)
    {
        return view ("users.profile", compact ("user"));
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
            "bio" => "sometimes|nullable|string",
            "general" => "sometimes|nullable|string",
            "music" => "sometimes|nullable|string",
            "movies" => "sometimes|nullable|string",
            "television" => "sometimes|nullable|string",
            "books" => "sometimes|nullable|string",
            "heroes" => "sometimes|nullable|string"
        ]);

        $user = auth ()->user ();
        $fname = $user->id . "-" . uniqid () . ".jpg";

        $changing_avatar = false;
        if (isset ($incoming_fields["avatar"]) && !empty ($incoming_fields["avatar"]))
        {
            $manager = new ImageManager (new Driver ());
            $image = $manager->read ($request->file ("avatar"));
            $image_data = $image->cover (256, 256)->toJpeg ();
            Storage::disk ("public")->put ("avatars/" . $fname, $image_data);

            $old_avatar = $user->avatar;
            $user->avatar = $fname;

            $changing_avatar = true;
        }

        $user->bio = $incoming_fields["bio"];
        $user->interests_general = $incoming_fields["general"];
        $user->interests_music = $incoming_fields["music"];
        $user->interests_movies = $incoming_fields["movies"];
        $user->interests_television = $incoming_fields["television"];
        $user->interests_books = $incoming_fields["books"];
        $user->interests_heroes = $incoming_fields["heroes"];
        $user->save ();

        if ($changing_avatar)
        {
            Storage::disk ("public")->delete (str_replace ("/storage/", "", $old_avatar));
        }

        return back ()->with ("success", "Profile updated successfully!");
    }
}
