@extends ("partials.layout")

@section ("title", "Search")

@section ("content")
<div class="simple-container">
    <h1>Search</h1>
    <label for="search">
        <p>Search for People, Posts and Hashtags using the following <b>field:</b></p>
    </label>
    <form method="GET">
        <input type="text" name="query" id="search" value="{{ request()->get('query') }}" placeholder="Search for People, Posts and Hashtags">
        <button type="submit">Search</button>
    </form>

    <br>

    @if (request ()->get ("query") != null)
        <div class="new-people">
            <div class="top">
                <h4>People</h4>
                <a class="more" href="#">[view all]</a>
            </div>

            <div class="inner">
                @forelse ($users as $user)
                    <x-user_block :user="$user" />
                @empty
                    <p><i>No users found.</i></p>
                @endforelse
            </div>
        </div>

        <br>

        <div class="new-people">
            <div class="top">
                <h4>Hashtags</h4>
                <a class="more" href="#">[view all]</a>
            </div>

            <div class="inner">
                @if (count ($hashtags) == 0)
                    <p><i>No hashtags found.</i></p>
                @else
                    <x-tag_cloud :hashtags="$hashtags" />
                @endif
            </div>
        </div>

        <br>

        <h1>Posts</h1>
        <table class="comments-table" cellspacing="0" cellpadding="3" bordercollor="#ffffff" border="1">
            <tbody>
                @forelse ($posts as $post)
                    <x-comment_block :post="$post" />
                @empty
                    <p><i>No posts found.</i></p>
                @endforelse
            </tbody>
        </table>
        {{ $posts->withQueryString ()->links ("pagination::default") }}

    @endif
</div>
@endsection
