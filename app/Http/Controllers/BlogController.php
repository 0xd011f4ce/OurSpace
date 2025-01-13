<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Events\BlogCreatedEvent;

use App\Models\BlogCategory;

use App\Helpers\PaginationHelper;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    public function index ()
    {
        $categories = BlogCategory::all ();
        $user = null;

        if (auth ()->check ())
            $user = auth ()->user ();

        $blogs = Blog::orderBy ("created_at", "desc")->paginate (10);

        return view ("blogs", compact ("user", "blogs", "categories"));
    }

    public function create ()
    {
        $categories = BlogCategory::all ();

        return view ("blogs.create", compact ("categories"));
    }

    public function store (Request $request)
    {
        if (!auth ()->check ())
            return redirect ()->route ("login")->with ("error", "You must be logged in to create a blog.");

        $request->validate ([
            "name" => "required|unique:users|unique:blogs",
            "description" => "required",
            "icon" => "required|image|max:4096",
            "category" => "required"
        ]);

        $user = auth ()->user ();

        $category = BlogCategory::find ($request->category);
        if (!$category)
            return redirect ()->route ("blogs.create")->with ("error", "Invalid category selected.");

        $icon = null;
        $fname = $user->id . "-" . uniqid();
        if ($request->icon)
        {
            $manager = new ImageManager (new Driver ());
            $image = $manager->read ($request->file ("icon"));
            $image_data = $image->cover (256, 256)->toJpeg ();
            Storage::disk ("public")->put ("blog_icons/" . $fname . ".jpg", $image_data);
        }

        $blog = Blog::create ([
            "name" => $request ["name"],
            "slug" => Str::slug ($request ["name"]),
            "description" => Str::markdown($request ["description"]),
            "icon" => $fname . ".jpg",
            "user_id" => $user->id,
            "blog_category_id" => $category->id
        ]);

        BlogCreatedEvent::dispatch ($blog, $user);

        return redirect ()->route ("blogs.show", [ 'blog' => $blog->slug ])->with ("success", "Blog created successfully!");
    }

    public function show (Blog $blog)
    {
        $notes = PaginationHelper::paginate ($blog->notes ()->orderBy ("created_at", "desc")->get (), 10);

        return view ("blogs.show", compact ("blog", "notes"));
    }

    public function new_entry (Blog $blog)
    {
        return view ("blogs.new_entry", compact ("blog"));
    }
}
