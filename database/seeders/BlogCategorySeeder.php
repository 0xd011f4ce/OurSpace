<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class BlogCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                "name" => "Art",
                "slug" => "art"
            ],
            [
                "name" => "Automotive",
                "slug" => "automotive"
            ],
            [
                "name" => "Fashion",
                "slug" => "fashion"
            ],
            [
                "name" => "Financial",
                "slug" => "financial"
            ],
            [
                "name" => "Food",
                "slug" => "food"
            ],
            [
                "name" => "Games",
                "slug" => "games"
            ],
            [
                "name" => "Life",
                "slug" => "life"
            ],
            [
                "name" => "Literature",
                "slug" => "literature"
            ],
            [
                "name" => "Math & Science",
                "slug" => "math-science"
            ],
            [
                "name" => "Movies & TV",
                "slug" => "movies-tv"
            ],
            [
                "name" => "Music",
                "slug" => "music"
            ],
            [
                "name" => "Paranormal",
                "slug" => "paranormal"
            ],
            [
                "name" => "Politics",
                "slug" => "politics"
            ],
            [
                "name" => "Humanity",
                "slug" => "humanity"
            ],
            [
                "name" => "Romance",
                "slug" => "romance"
            ],
            [
                "name" => "Sports",
                "slug" => "sports"
            ],
            [
                "name" => "Technology",
                "slug" => "technology"
            ],
            [
                "name" => "Travel",
                "slug" => "travel"
            ]
        ];

        foreach ($categories as $category) {
            DB::table("blog_categories")->insert($category);
        }
    }
}
