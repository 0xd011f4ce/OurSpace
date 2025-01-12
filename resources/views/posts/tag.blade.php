@extends ("partials.layout")

@section ("title", "Posts Tagged with " . $hashtag->name)

@section ("content")
<div class="simple-container">
    <h1>Showing posts with tag: {{ $hashtag->name }}</h1>
    <br>

    <table class="comments-table" cellspacing="0" cellpadding="3" bordercollor="#ffffff" border="1">
        <tbody>
            @foreach ($posts as $post)
                <x-comment_block :post="$post" />
            @endforeach
        </tbody>
    </table>

    {{ $posts->links("pagination::default") }}
</div>
@endsection
