@extends ("partials.layout")

@section ("title", "Blogs")

@section ("content")
    <div class="row blog-category">
        <div class="col w-20 left">
            <div class="category-list">
                <b>View:</b>

                <ul>
                    <li>
                        <a href="{{ route ('blogs') }}">
                            <img loading="lazy" src="/resources/icons/clock.png" class="icon">
                            <b>Recent Blogs</b>
                        </a>
                    </li>

                    {{-- TODO: Top entries and blogs I'm following --}}
                </ul>

                <b>Categories:</b>
                <ul>
                    @foreach ($categories as $category)
                        <li>
                            <a href="#">{{ $category->name }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="col right">
            <h1>Blogs</h1>

            @auth
                <div class="blog-preview">
                    <h3>
                        [
                            <a href="{{ route ('blogs.create') }}">Create a blog</a>
                        ]
                    </h3>
                    <h3>
                        [
                            <a href="#">View your blogs</a>
                        ]
                    </h3>
                </div>
            @endauth

            <hr>

            <h3>Latest Blogs</h3>
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

                {{ $blogs->links ("pagination::default") }}
            </div>
        </div>
    </div>
@endsection
