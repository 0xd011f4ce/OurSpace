@extends ("partials.layout")

@section ("title", "Explore")

@section ("content")
    <div class="simple-container">
        <h1>Browse Users</h1>

        <p>
            Filter:
            <a @if(request ()->get("users") == null) class="filter-active" @endif href="?users=">Local</a>
            |
            <a @if(request ()->get("users") == "all") class="filter-active" @endif href="?users=all">All</a>
        </p>

        <div class="new-people">
            <div class="top">
                <h4>Active Users</h4>
                <a href="#" class="more">[random]</a>
            </div>

            <div class="inner">
                @foreach ($users as $user)
                    <x-user_block :user="$user" />
                @endforeach
            </div>
        </div>

        <h1>Popular Hashtags</h1>

        <div class="new-people">
            <div class="top">
                <h4>Hashtags</h4>
            </div>

            <div class="inner">
                <x-tag_cloud :hashtags="$popular_hashtags" />
            </div>
        </div>

        <h1>Posts</h1>
        <p>
            Filter:
            <a @if(request ()->get("posts") == null) class="filter-active" @endif href="?posts=">Trending</a>
            |
            <a @if(request ()->get("posts") == "latest") class="filter-active" @endif href="?posts=latest">Newest</a>
        </p>

        <table class="comments-table" cellspacing="0" cellpadding="3" bordercollor="#ffffff" border="1">
            <tbody>
                @foreach ($notes as $post)
                    <x-comment_block :post="$post" />
                @endforeach
            </tbody>
        </table>

        {{ $notes->links("pagination::default") }}
    </div>
@endsection
