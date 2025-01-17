@php
    $user_url = null;

    if ($actor->blog_id)
    {
        $user_url = route ('blogs.show', [ 'blog' => $actor->blog->slug ]);
    }
    else if ($actor->user_id)
    {
        $user_url = route ('users.show', [ 'user_name' => $actor->user->name ]);
    }
    else
    {
        $user_url = route ('users.show', [ 'user_name' => $actor->local_actor_id ]);
    }
@endphp

@extends("partials.layout")

@section ("title", "View Post")

@section ("content")

<div class="row article blog-entry">
    <div class="col w-20 left">
        <div class="edit-info">
            <div class="profile-pic">
                <img loading="lazy" src="{{ $actor->icon }}" class="pfp-fallback">
            </div>

            <div class="author-details">
                <h4>
                    Published by
                    <span>
                        <a href="{{ $user_url }}">
                            {{ $actor->name }}
                        </a>
                    </span>
                </h4>

                <p class="publish-date">
                    published <time class="ago">
                        {{ $note->created_at->diffForHumans() }}
                    </time>
                    <br>
                </p>

                <p class="links">
                    <a href="{{ $user_url }}">
                        <img loading="lazy" src="/resources/icons/user.png" class="icon">
                        <span class="m-hide">View</span> {{ $actor->blog_id ? "Blog" : "Profile" }}
                    </a>
                </p>
            </div>
        </div>
    </div>

    <div class="col right">
        <h1 class="title">{{ $actor->name }}'s Post</h1>
        @if (auth ()->check () && auth ()->user ()->is ($actor->user))
        <div class="buttons" style="display: flex; gap: 5px;">
            <form action="#" method="POST">
                @csrf
                @method("DELETE")
                <a href="{{ route ('posts.edit', [ 'note' => $note ]) }}">
                    <button type="button">Edit</button>
                </a>
                <button type="submit">Delete</button>
            </form>

            <form action="{{ route ('posts.pin', [ 'note' => $note ]) }}" method="POST">
                @csrf
                <button type="submit">{{ $note->is_pinned ($actor) ? "Unpin" : "Pin" }}</button>
            </form>
        </div>
        @endif

        @if ($note->in_reply_to)
            <p>
                <b>In Reply To</b>: <a href="{{ route ('posts.show', [ 'note' => $note->get_parent ()->first ()->id ]) }}">this post</a>
            </p>
        @endif

        <div class="content">
            <div class="heading">
                <h4>{{ $note->summary }}</h4>
            </div>
            {!! $note->content !!}

            @foreach ($note->attachments as $attachment)
                {{-- check if $attachment->media_type starts with image/ --}}
                @if (str_starts_with ($attachment->media_type, "image/") || $attachment->media_type == null)
                    <img loading="lazy" src="{{ $attachment->url }}" width="250" class="expandable">
                @else
                    <p><i>Attachment {{ $attachment->media_type }} is not supported yet</i></p>
                @endif
            @endforeach
        </div>

        <br>

        @auth
            <div class="buttons" style="display: flex; gap: 10px;">
                <form action="{{ route ('posts.like', [ 'note' => $note->id ]) }}" method="POST">
                    @csrf
                    <button type="submit">{{ auth ()->user ()->actor ()->first ()->liked_note ($note) ? "Undo Like" : "Like" }}</button>
                </form>

                <form action="{{ route ('posts.boost', [ 'note' => $note->id ]) }}" method="POST">
                    @csrf
                    <button type="submit">{{ auth ()->user ()->actor ()->first ()->boosted_note ($note) ? "Unboost" : "Boost" }}</button>
                </form>
            </div>
        @endauth

        <p>
            <b>Likes</b>: {{ $note->get_likes ()->count () }}<br>
            <b>Boosts</b>: {{ $note->get_boosts ()->count () }}<br>
            <b>Hashtags:</b>
            <span>
                @foreach ($note->get_hashtags ()->get () as $hashtag)
                    <a href="{{ route ('tags', [ 'tag' => $hashtag->name ]) }}">
                        {{ $hashtag->name }}
                    </a>
                @endforeach
            </span><br>
            <b>Mentions:</b>
            <span>
                @foreach ($note->get_mentions ()->get () as $mention)
                    <a href="{{ route ('users.show', [ 'user_name' => $mention->actor->local_actor_id ?? $mention->actor->preferredUsername ]) }}">
                        {{ $mention->actor->local_actor_id ?? '@' . $mention->actor->preferredUsername }}
                    </a>
                @endforeach
            </span>
        </p>

        <div class="comments" id="comments">
            <div class="heading">
                <h4>Comments</h4>
            </div>

            <div class="inner">
                @auth
                    <x-create_note :inreplyto="$note" />
                    <br>
                @endauth

                <p>
                    <b>Displaying <span class="count">0</span> of <span class="count">{{ $note->get_replies ()->count () }}</span> comments</b>
                </p>

                <table class="comments-table" cellspacing="0" cellpadding="3" bordercolor="ffffff" border="1">
                    <tbody>
                        @foreach ($note->get_replies ()->get () as $reply)
                            <x-comment_block :post="$reply" />
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
