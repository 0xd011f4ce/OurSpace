@extends ("partials.layout")

@section ("title", $user->name . "'s Blogs")

@section ("content")
    <div class="simple-container">
        <h1>{{ $user->name }}'s Blogs</h1>
        <p>
            <a href="{{ route ('users.show', [ 'user_name' => $user->name ]) }}">&laquo; Back to profile</a>
        </p>

        <br>

        <div class="blog-entries">
            @foreach ($blogs as $blog)
                <div class="entry">
                    <p class="publish-date">
                        <time class="ago">{{ $blog->created_at->diffForHumans () }}</time>
                        &mdash; by <a href="{{ route ('users.show', [ 'user_name' => $blog->user->name ]) }}">{{ $blog->user->name }}</a>
                        &mdash; <a>{{ count ($blog->notes) }} Posts</a>
                    </p>

                    <div class="inner">
                        <h3 class="title">
                            <a href="{{ route ('blogs.show', [ 'blog' => $blog ]) }}">
                                {{ $blog->name }}
                            </a>
                        </h3>

                        <p>
                            {!! $blog->description !!}
                        </p>

                        <a href="{{ route ('blogs.show', [ 'blog' => $blog ]) }}">
                            &raquo; Read more
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
